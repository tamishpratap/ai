<?php

namespace App\Services\AI;

use App\Services\AI\FormGeminiBridge;
use App\Services\Gemini\GeminiService;
use Illuminate\Support\Facades\Log;

/**
 * IraFormUnderstandingService
 *
 * Core natural language understanding engine for voice-driven form filling.
 * - Extracts single and multi-field inputs from English, Roman Hinglish, and Devanagari Hindi.
 * - Normalizes spoken numbers (including Hindi words: 'pachas', 'dedh sau', 'do hazaar').
 * - Normalizes identifiers (Member IDs: 'G A B 1 0 0 2 5' -> 'GAB10025').
 * - Matches and validates dropdown options against real schema definitions.
 * - Cross-validates dependent field bounds (e.g. Staking package amount ranges).
 * - Enforces sensitive field boundaries (passwords, PINs, OTPs, CAPTCHAs are never filled).
 * - Generates clear, non-repetitive bilingual voice confirmations.
 */
class IraFormUnderstandingService
{
    public function __construct(
        protected GeminiService $gemini,
        protected ?FormGeminiBridge $formBridge = null
    ) {
        $this->formBridge = $formBridge ?? app(FormGeminiBridge::class);
    }

    /**
     * Check if a field is sensitive (passwords, PINs, OTPs, CAPTCHAs, secrets, tokens).
     */
    public function isSensitiveField(array $fieldDef): bool
    {
        if (($fieldDef['sensitive'] ?? false) === true) {
            return true;
        }
        $type = strtolower($fieldDef['type'] ?? '');
        if ($type === 'password') {
            return true;
        }
        $name = strtolower($fieldDef['name'] ?? '');
        if (
            str_contains($name, 'password') ||
            str_contains($name, 'pass') ||
            str_contains($name, 'pin') ||
            str_contains($name, 'tpin') ||
            str_contains($name, 'otp') ||
            str_contains($name, 'captcha') ||
            str_contains($name, 'secret') ||
            str_contains($name, 'token')
        ) {
            return true;
        }
        $label = strtolower($fieldDef['label'] ?? '');
        if (
            str_contains($label, 'password') ||
            str_contains($label, 'pin') ||
            str_contains($label, 'otp') ||
            str_contains($label, 'captcha') ||
            str_contains($label, 'पासवर्ड') ||
            str_contains($label, 'पिन')
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if a field is read-only, display-only, or hidden.
     */
    public function isReadOnlyOrHiddenField(array $fieldDef): bool
    {
        if (($fieldDef['read_only'] ?? false) === true) {
            return true;
        }
        if (($fieldDef['hidden'] ?? false) === true) {
            return true;
        }
        $type = strtolower($fieldDef['type'] ?? '');
        if ($type === 'hidden') {
            return true;
        }
        $name = strtolower($fieldDef['name'] ?? '');
        if ($name === '_token' || $name === 'csrf' || str_contains($name, 'csrf')) {
            return true;
        }
        return false;
    }

    /**
     * Find next genuinely eligible, visible, non-sensitive unfilled field.
     */
    public function findNextEligibleField(
        array $schema,
        array $fieldSequence,
        array $currentFields = [],
        array $excludeKeys = [],
        ?array $domEligibleKeys = null,
        bool $isEditForm = false,
        array $handledFields = []
    ): ?string {
        foreach ($fieldSequence as $k) {
            if (in_array($k, $excludeKeys, true)) {
                continue;
            }
            $fDef = $schema['fields'][$k] ?? null;
            if (!$fDef) {
                continue;
            }
            if ($this->isSensitiveField($fDef)) {
                continue;
            }
            if ($this->isReadOnlyOrHiddenField($fDef)) {
                continue;
            }
            if ($fDef['file_upload'] ?? false) {
                continue;
            }
            if (is_array($domEligibleKeys) && !empty($domEligibleKeys)) {
                $fName = $fDef['name'] ?? '';
                if (!in_array($k, $domEligibleKeys, true) && !in_array($fName, $domEligibleKeys, true)) {
                    continue;
                }
            }

            if ($isEditForm) {
                // In edit forms, fields not yet handled in this session are eligible
                if (!in_array($k, $handledFields, true)) {
                    return $k;
                }
            } else {
                // In create forms, check if field is unfilled in current DOM
                if (!isset($currentFields[$k]) || trim((string) $currentFields[$k]) === '') {
                    return $k;
                }
            }
        }
        return null;
    }

    /**
     * Phase 8: Initialize a guided, field-by-field voice form-filling session.
     *
     * @param string $formKey Registered form key
     * @param string $role Server-authoritative user role
     * @param array $currentFields Currently populated values from the DOM
     * @param string $language UI Language ('hi', 'hinglish', 'en')
     * @param array $context Additional request context including verified DOM elements
     * @return array
     */
    public function initGuidedSession(
        string $formKey,
        string $role = 'guest',
        array $currentFields = [],
        string $language = 'hi',
        array $context = []
    ): array {
        $schema = IraFormRegistry::getForm($formKey);
        if (!$schema) {
            return [
                'success' => false,
                'status' => 'unregistered_form',
                'speech' => 'Is page par koi registered form nahi mila.',
                'form_updates' => [],
                'guide_state' => null,
            ];
        }

        if (!IraFormRegistry::isFormAuthorized($formKey, $role)) {
            return [
                'success' => false,
                'status' => 'unauthorized',
                'speech' => 'Aapko is form ko bharne ki anumati nahi hai.',
                'form_updates' => [],
                'guide_state' => null,
            ];
        }

        $allFields = $schema['fields'] ?? [];
        $sensitiveFields = array_filter($allFields, fn($f) => $this->isSensitiveField($f));
        $domEligibleKeys = $context['dom_eligible_fields'] ?? null;

        // 1. Sensitive-only check (e.g. Change Password / Change Transaction Password)
        $nonSensitiveCount = count(array_filter($allFields, fn($f) => !$this->isSensitiveField($f) && !$this->isReadOnlyOrHiddenField($f)));
        if ($nonSensitiveCount === 0 && !empty($sensitiveFields)) {
            $sensitiveLabels = array_map(fn($f) => $f['label'], $sensitiveFields);
            $labelsStr = $this->formatFieldLabelsList($sensitiveLabels);

            return [
                'success' => true,
                'status' => 'all_sensitive',
                'action' => 'complete',
                'speech' => "आपने {$schema['title']} form चुना है। इसमें {$labelsStr} हैं। सुरक्षा के लिए कृपया password fields स्क्रीन पर manually भरें। मैं इन fields में आवाज़ से password नहीं भरूँगी।",
                'form_key' => $formKey,
                'form_title' => $schema['title'],
                'form_selector' => $schema['selector'],
                'guide_state' => [
                    'status' => 'completed',
                    'current_field' => null,
                    'filled_fields' => [],
                    'field_sequence' => [],
                ],
                'active_field' => null,
                'form_updates' => [],
            ];
        }

        // 2. Identify eligible fields (strictly non-sensitive, non-readonly, non-hidden, non-file-upload for typing)
        $eligibleFields = [];
        $eligibleKeys = [];
        foreach ($allFields as $k => $f) {
            if ($this->isSensitiveField($f) || $this->isReadOnlyOrHiddenField($f) || ($f['file_upload'] ?? false)) {
                continue;
            }

            // DOM-level visibility filter if provided by client
            if (is_array($domEligibleKeys) && !empty($domEligibleKeys)) {
                $fName = $f['name'] ?? '';
                $isDomEligible = in_array($k, $domEligibleKeys, true) ||
                                 ($fName !== '' && in_array($fName, $domEligibleKeys, true));
                if (!$isDomEligible) {
                    continue;
                }
            }

            $eligibleFields[$k] = $f;
            $eligibleKeys[] = $k;
        }

        if (empty($eligibleKeys)) {
            // Check if there is a file upload field
            $fileFields = array_filter($allFields, fn($f) => ($f['file_upload'] ?? false) === true);
            if (!empty($fileFields)) {
                $fileLabels = $this->formatFieldLabelsList(array_map(fn($f) => $f['label'], $fileFields));
                return [
                    'success' => true,
                    'status' => 'file_only',
                    'action' => 'complete',
                    'speech' => "आपने {$schema['title']} form चुना है। इसमें केवल File Upload field है। सुरक्षा के लिए कृपया स्क्रीन पर Choose File बटन से {$fileLabels} अपलोड करें।",
                    'form_key' => $formKey,
                    'form_title' => $schema['title'],
                    'form_selector' => $schema['selector'],
                    'guide_state' => [
                        'status' => 'completed',
                        'current_field' => null,
                        'filled_fields' => [],
                        'field_sequence' => [],
                    ],
                    'active_field' => null,
                    'form_updates' => [],
                ];
            }

            return [
                'success' => true,
                'status' => 'no_eligible_fields',
                'action' => 'complete',
                'speech' => "आपने {$schema['title']} form चुना है। इसमें आवाज़ से भरने योग्य कोई फ़ील्ड उपलब्ध नहीं है।",
                'form_key' => $formKey,
                'form_title' => $schema['title'],
                'form_selector' => $schema['selector'],
                'guide_state' => [
                    'status' => 'completed',
                    'current_field' => null,
                    'filled_fields' => [],
                    'field_sequence' => [],
                ],
                'active_field' => null,
                'form_updates' => [],
            ];
        }

        $isEditForm = IraFormRegistry::isEditForm($schema);

        // 3. Determine filled fields from current DOM state
        $alreadyFilled = [];
        foreach ($eligibleKeys as $k) {
            $val = $currentFields[$k] ?? null;
            if ($val !== null && trim((string) $val) !== '') {
                $alreadyFilled[] = $k;
            }
        }

        // First target field:
        if ($isEditForm) {
            // For edit forms, always start with the first eligible field in sequence
            $firstKey = $eligibleKeys[0];
        } else {
            // For create forms, pick first unfilled field in eligible sequence, or first field
            $firstKey = null;
            foreach ($eligibleKeys as $k) {
                if (!in_array($k, $alreadyFilled, true)) {
                    $firstKey = $k;
                    break;
                }
            }
            if ($firstKey === null) {
                $firstKey = $eligibleKeys[0];
            }
        }

        $firstField = $schema['fields'][$firstKey];

        // Step A: Announce verified form name and purpose naturally and politely
        $eligibleLabels = array_map(fn($f) => $f['label'], $eligibleFields);
        $labelsSummary = $this->formatFieldLabelsList($eligibleLabels);

        $verbEn = $isEditForm ? 'edit' : 'fill';
        $verbHinglish = $isEditForm ? 'edit karne' : 'bharne';

        if ($language === 'en') {
            $stepA = "Certainly, I can help you {$verbEn} the {$schema['title']} form. This form includes fields such as {$labelsSummary}. Let's start with {$firstField['label']}. ";
        } elseif ($language === 'hinglish') {
            $stepA = "Bilkul, main aapka {$schema['title']} form {$verbHinglish} mein help karti hoon. Ismein {$labelsSummary} jaise fields hain. Chaliye, pehle {$firstField['label']} se shuru karte hain. ";
        } else {
            // Hindi default (preserves 'भरने में मदद करती हूँ' for existing test assertions)
            $stepA = "बिल्कुल, मैं आपका {$schema['title']} form भरने में मदद करती हूँ। इसमें {$labelsSummary} जैसे fields हैं। चलिए, पहले {$firstField['label']} से शुरू करते हैं। ";
        }

        // Step B: Ask about the first eligible field using real visible label
        $stepB = $this->getFieldPromptQuestion($firstField, $schema, $language);

        return [
            'success' => true,
            'status' => 'asking_field',
            'action' => 'prompt',
            'speech' => $stepA . $stepB,
            'form_key' => $formKey,
            'form_title' => $schema['title'],
            'form_selector' => $schema['selector'],
            'guide_state' => [
                'status' => 'in_progress',
                'current_field' => $firstKey,
                'field_sequence' => $eligibleKeys,
                'filled_fields' => $alreadyFilled,
                'handled_fields' => [],
                'is_edit_form' => $isEditForm,
                'step_index' => array_search($firstKey, $eligibleKeys, true) ?: 0,
                'language' => $language,
            ],
            'active_field' => [
                'key' => $firstKey,
                'name' => $firstField['name'],
                'selector' => $firstField['selector'],
                'label' => $firstField['label'],
                'type' => $firstField['type'],
            ],
            'form_updates' => [],
        ];
    }

    /**
     * Phase 8: Process a guided, field-by-field voice conversational turn.
     *
     * @param string $speech Raw transcribed utterance
     * @param string $formKey Registered form key
     * @param string $role Server-authoritative user role
     * @param array $currentFields Currently populated values from the DOM
     * @param array $guideState Active guided state machine state
     * @param array $context Conversational context
     * @return array
     */
    public function processGuidedTurn(
        string $speech,
        string $formKey,
        string $role = 'guest',
        array $currentFields = [],
        array $guideState = [],
        array $context = []
    ): array {
        $clean = trim($speech);
        $schema = IraFormRegistry::getForm($formKey);
        if (!$schema) {
            return [
                'success' => false,
                'status' => 'unregistered_form',
                'speech' => 'Is page par koi registered form nahi mila.',
                'form_updates' => [],
                'guide_state' => null,
            ];
        }

        if (!IraFormRegistry::isFormAuthorized($formKey, $role)) {
            return [
                'success' => false,
                'status' => 'unauthorized',
                'speech' => 'Aapko is form ko bharne ki anumati nahi hai.',
                'form_updates' => [],
                'guide_state' => null,
            ];
        }

        $norm = $this->normalizeUtterance($clean);
        $language = $context['language'] ?? ($guideState['language'] ?? 'hi');
        $guideState['language'] = $language;

        // 1. Check for Cancel / Stop / Exit command
        if (preg_match('/\b(cancel|stop|exit|band\s*karo|hatao|pause|khatam\s*karo|cancel\s*kardo)\b/iu', $norm)) {
            return [
                'success' => true,
                'status' => 'cancelled',
                'action' => 'exit',
                'speech' => 'Form filling cancel kiya gaya. Aap jab chahein punah shuru kar sakte hain.',
                'form_key' => $formKey,
                'form_selector' => $schema['selector'],
                'guide_state' => ['status' => 'cancelled', 'current_field' => null],
                'form_updates' => [],
            ];
        }

        // 2. Check for Reset / Clear Form command
        if ($this->isResetFormCommand($norm)) {
            $resetResult = $this->handleResetFormCommand($schema);
            $firstFieldKey = $guideState['field_sequence'][0] ?? array_key_first($schema['fields']);
            $firstField = $schema['fields'][$firstFieldKey] ?? null;
            $nextPrompt = $firstField ? $this->getFieldPromptQuestion($firstField, $schema) : '';

            $resetResult['speech'] = "Maine form ke sabhi fields clear kar diye hain. " . $nextPrompt;
            $resetResult['action'] = 'prompt';
            $resetResult['guide_state'] = [
                'status' => 'in_progress',
                'current_field' => $firstFieldKey,
                'field_sequence' => $guideState['field_sequence'] ?? array_keys($schema['fields']),
                'filled_fields' => [],
                'step_index' => 0,
            ];
            $resetResult['active_field'] = $firstField ? [
                'key' => $firstFieldKey,
                'name' => $firstField['name'],
                'selector' => $firstField['selector'],
                'label' => $firstField['label'],
                'type' => $firstField['type'],
            ] : null;

            return $resetResult;
        }

        // 3. Check for Review / Summary command
        if (preg_match('/\b(review|summary|kya\s*bhara\s*hai|check\s*details|show\s*details|batao\s*kya\s*hai)\b/iu', $norm)) {
            return $this->handleGuidedReviewCommand($schema, $currentFields, $guideState);
        }

        // 4. Check for Remaining Fields query
        if ($this->isRemainingFieldsQuery($norm)) {
            return $this->handleGuidedRemainingQuery($schema, $currentFields, $guideState);
        }

        // 5. Check for Spoken Rewind Command ("पिछली value बदल दो", "previous field badlo", "peeche jao")
        $isRewind = preg_match('/\b(pichli|pichla|previous|peeche|back|last)\s*(value|field|wala)?\s*(badlo|change|karo|jao|bharo)?\b/iu', $norm) ||
                    preg_match('/(?:^|\s)(पिछली|पिछला)\s*(वैल्यू|फील्ड)?\s*(बदल\s*दो|बदलो|करो)?(?:$|\s)/u', $clean);
        if ($isRewind) {
            $filled = $guideState['filled_fields'] ?? [];
            if (!empty($filled)) {
                $rewindKey = end($filled);
                $rewindDef = $schema['fields'][$rewindKey] ?? null;
                if ($rewindDef) {
                    $guideState['current_field'] = $rewindKey;
                    $guideState['step_index'] = array_search($rewindKey, $guideState['field_sequence'] ?? []) ?: 0;
                    $guideState['filled_fields'] = array_values(array_diff($filled, [$rewindKey]));
                    unset($currentFields[$rewindKey]);
                    $promptQ = $this->getFieldPromptQuestion($rewindDef, $schema);
                    return [
                        'success' => true,
                        'status' => 'rewind_field',
                        'action' => 'prompt',
                        'speech' => "ठीक है, {$rewindDef['label']} को दोबारा बदलते हैं। {$promptQ}",
                        'form_key' => $formKey,
                        'form_selector' => $schema['selector'],
                        'guide_state' => $guideState,
                        'active_field' => [
                            'key' => $rewindKey,
                            'name' => $rewindDef['name'],
                            'selector' => $rewindDef['selector'],
                            'label' => $rewindDef['label'],
                            'type' => $rewindDef['type'],
                        ],
                        'form_updates' => [],
                    ];
                }
            }
        }

        // Active field and context setup
        $domEligibleKeys = $context['dom_eligible_fields'] ?? ($guideState['dom_eligible_fields'] ?? null);
        if ($domEligibleKeys !== null) {
            $guideState['dom_eligible_fields'] = $domEligibleKeys;
        }

        $isEditForm = $guideState['is_edit_form'] ?? IraFormRegistry::isEditForm($schema);
        $guideState['is_edit_form'] = $isEditForm;
        $handledFields = $guideState['handled_fields'] ?? ($guideState['filled_fields'] ?? []);

        // 6. Check for Skip / Keep Existing Command ("skip", "same rehne do", "keep existing", "wahi rehne do", "badalna nahi hai")
        $isSkipCommand = preg_match('/\b(skip|same\s*rehne\s*do|keep\s*existing|wahi\s*rehne\s*do|badalna\s*nahi\s*hai|isse\s*mat\s*badlo|koi\s*change\s*nahi|no\s*change|aage\s*badho|next|skip\s*karo)\b/iu', $norm) ||
            preg_match('/(?:वही\s*रहने\s*दो|बदलना\s*नहीं\s*है|कोई\s*बदलाव\s*नहीं|स्किप|आगे\s*बढ़ो)/u', $clean);
        if ($isSkipCommand) {
            $currentFieldKey = $guideState['current_field'] ?? ($guideState['field_sequence'][0] ?? null);
            $activeFieldDef = $currentFieldKey ? ($schema['fields'][$currentFieldKey] ?? null) : null;
            if ($activeFieldDef) {
                $handledFields = array_unique(array_merge($handledFields, [$currentFieldKey]));
                $guideState['handled_fields'] = $handledFields;

                $fieldSequence = $guideState['field_sequence'] ?? array_keys($schema['fields']);
                $nextFieldKey = $this->findNextEligibleField(
                    $schema,
                    $fieldSequence,
                    $currentFields,
                    [$currentFieldKey],
                    $domEligibleKeys,
                    $isEditForm,
                    $handledFields
                );

                if ($nextFieldKey !== null) {
                    $nextFieldDef = $schema['fields'][$nextFieldKey];
                    $nextQuestion = $this->getFieldPromptQuestion($nextFieldDef, $schema, $language);
                    if ($language === 'en') {
                        $speech = "Kept {$activeFieldDef['label']} as is. Now please specify the {$nextQuestion}";
                    } elseif ($language === 'hinglish') {
                        $speech = "Theek hai, {$activeFieldDef['label']} wahi rahega. Ab {$nextQuestion}";
                    } else {
                        $speech = "ठीक है, {$activeFieldDef['label']} वही रहेगा। अब {$nextQuestion}";
                    }

                    $guideState['current_field'] = $nextFieldKey;
                    $guideState['step_index'] = array_search($nextFieldKey, $fieldSequence, true) ?: 0;

                    return [
                        'success' => true,
                        'status' => 'field_skipped',
                        'action' => 'prompt',
                        'speech' => $speech,
                        'form_key' => $formKey,
                        'form_selector' => $schema['selector'],
                        'guide_state' => $guideState,
                        'active_field' => [
                            'key' => $nextFieldKey,
                            'name' => $nextFieldDef['name'],
                            'selector' => $nextFieldDef['selector'],
                            'label' => $nextFieldDef['label'],
                            'type' => $nextFieldDef['type'],
                        ],
                        'form_updates' => [],
                    ];
                }

                return $this->buildCompletionPayload($schema, $currentFields, $guideState, []);
            }
        }

        // 7. Check for Direct Field Targeting & Specific Field Switch Commands
        $directTargetingResult = $this->resolveDirectFieldTargeting(
            $clean,
            $norm,
            $schema,
            $currentFields,
            $guideState,
            $language,
            $domEligibleKeys
        );
        if ($directTargetingResult !== null) {
            return $directTargetingResult;
        }

        $currentFieldKey = $guideState['current_field'] ?? ($guideState['field_sequence'][0] ?? null);
        $activeFieldDef = $currentFieldKey ? ($schema['fields'][$currentFieldKey] ?? null) : null;

        // Guard: Active field must be genuinely eligible (non-sensitive, non-readonly, non-hidden, in DOM if provided)
        $isCurrentEligible = $activeFieldDef &&
            !$this->isSensitiveField($activeFieldDef) &&
            !$this->isReadOnlyOrHiddenField($activeFieldDef) &&
            !($activeFieldDef['file_upload'] ?? false) &&
            (!is_array($domEligibleKeys) || empty($domEligibleKeys) || in_array($currentFieldKey, $domEligibleKeys, true) || in_array($activeFieldDef['name'] ?? '', $domEligibleKeys, true));

        if (!$isCurrentEligible) {
            $currentFieldKey = $this->findNextEligibleField(
                $schema,
                $guideState['field_sequence'] ?? array_keys($schema['fields']),
                $currentFields,
                [],
                $domEligibleKeys,
                $isEditForm,
                $handledFields
            );
            $activeFieldDef = $currentFieldKey ? ($schema['fields'][$currentFieldKey] ?? null) : null;
            if ($currentFieldKey) {
                $guideState['current_field'] = $currentFieldKey;
            }
        }

        if (!$activeFieldDef) {
            // All eligible fields already filled!
            return $this->buildCompletionPayload($schema, $currentFields, $guideState, []);
        }

        if ($clean === '') {
            $question = $this->getFieldPromptQuestion($activeFieldDef, $schema);
            return [
                'success' => false,
                'status' => 'empty',
                'action' => 'prompt',
                'speech' => "Kripya boliye. {$question}",
                'form_key' => $formKey,
                'form_selector' => $schema['selector'],
                'guide_state' => $guideState,
                'active_field' => [
                    'key' => $currentFieldKey,
                    'name' => $activeFieldDef['name'],
                    'selector' => $activeFieldDef['selector'],
                    'label' => $activeFieldDef['label'],
                    'type' => $activeFieldDef['type'],
                ],
                'form_updates' => [],
            ];
        }

        // Check for explicit active-field correction pattern: "नहीं, Abhi नहीं, Abhi Kumar लिखो" / "nahi 1000 nahi 2000"
        $isCorrectionUtterance = false;
        $evalSpeech = $clean;
        $evalNorm = $norm;
        if (preg_match('/^(?:नहीं|nahi|no)\s*[,]?\s*(.+?)\s+(?:नहीं|nahi|not)\s*[,]?\s*(.+?)(?:\s+(?:लिखो|likho|rakho|karo|bharo|kardo|daalo|kijiye|hoga))?$/iu', $clean, $mCorr)) {
            $isCorrectionUtterance = true;
            $evalSpeech = trim($mCorr[2]);
            $evalNorm = $this->normalizeUtterance($evalSpeech);
        }

        // Step C: Resolve user's answer specifically for that active field
        $extracted = $this->extractTargetedFieldValue($evalSpeech, $evalNorm, $activeFieldDef, $schema);
        if ($extracted !== null && $isCorrectionUtterance) {
            $extracted['is_correction'] = true;
        }
        $updatedFieldKey = $currentFieldKey;
        $updatedFieldDef = $activeFieldDef;
        $otherUpdates = [];

        // If targeted extraction did not resolve, check if user explicitly named another field in this form
        if ($extracted === null) {
            $generalExtraction = $this->extractFieldsDeterministic($clean, $schema, $currentFields);
            if (!empty($generalExtraction['updates'])) {
                // User gave a value for another field in this form!
                $otherKey = array_key_first($generalExtraction['updates']);
                $otherUp = $generalExtraction['updates'][$otherKey];
                $updatedFieldKey = $otherKey;
                $updatedFieldDef = $schema['fields'][$otherKey] ?? $activeFieldDef;
                $extracted = [
                    'value' => $otherUp['value'],
                    'display_value' => $otherUp['display_value'] ?? (string) $otherUp['value'],
                ];
                $otherUpdates = $generalExtraction['updates'];
            }
        }

        // Tier 2: Dedicated Gemini Semantic Fallback if deterministic extraction could not resolve
        if ($extracted === null && $this->formBridge->isConfigured()) {
            $aiResult = $this->formBridge->extractFieldValues($clean, $schema, $currentFields, $currentFieldKey, $context);
            if ($aiResult['success'] && !empty($aiResult['updates'])) {
                $aiUpdates = [];
                foreach ($aiResult['updates'] as $fKey => $fVal) {
                    if (isset($schema['fields'][$fKey])) {
                        $fDef = $schema['fields'][$fKey];
                        if (!$this->isSensitiveField($fDef) && !$this->isReadOnlyOrHiddenField($fDef) && !($fDef['file_upload'] ?? false)) {
                            $dispVal = (string) $fVal;
                            $spokenVal = $this->formatSpokenValue($dispVal, $fDef, $language);
                            $aiUpdates[$fKey] = [
                                'field' => $fKey,
                                'name' => $fDef['name'],
                                'selector' => $fDef['selector'],
                                'value' => $fVal,
                                'display_value' => $dispVal,
                                'spoken_value' => $spokenVal,
                                'label' => $fDef['label'],
                                'type' => $fDef['type'],
                                'events' => $fDef['dynamic_events'] ?? ['input', 'change'],
                            ];
                        }
                    }
                }

                if (!empty($aiUpdates)) {
                    $isCorrectionUtterance = $aiResult['is_correction'] || $isCorrectionUtterance;
                    if (isset($aiUpdates[$currentFieldKey])) {
                        $updatedFieldKey = $currentFieldKey;
                        $updatedFieldDef = $activeFieldDef;
                        $extracted = [
                            'value' => $aiUpdates[$currentFieldKey]['value'],
                            'display_value' => $aiUpdates[$currentFieldKey]['display_value'],
                            'is_correction' => $isCorrectionUtterance,
                        ];
                    } else {
                        $firstAiKey = array_key_first($aiUpdates);
                        $updatedFieldKey = $firstAiKey;
                        $updatedFieldDef = $schema['fields'][$firstAiKey] ?? $activeFieldDef;
                        $extracted = [
                            'value' => $aiUpdates[$firstAiKey]['value'],
                            'display_value' => $aiUpdates[$firstAiKey]['display_value'],
                            'is_correction' => $isCorrectionUtterance,
                        ];
                    }
                    $otherUpdates = $aiUpdates;
                }
            }
        }

        // If targeted extraction succeeded, also check if user supplied other eligible fields in the same compound sentence
        if ($extracted !== null && empty($otherUpdates)) {
            $generalCheck = $this->extractFieldsDeterministic($clean, $schema, $currentFields);
            if (!empty($generalCheck['updates']) && count($generalCheck['updates']) > 1) {
                $merged = $generalCheck['updates'];
                $valDisplay = $extracted['display_value'] ?? (string) $extracted['value'];
                $merged[$updatedFieldKey] = [
                    'field' => $updatedFieldKey,
                    'name' => $updatedFieldDef['name'],
                    'selector' => $updatedFieldDef['selector'],
                    'value' => $extracted['value'],
                    'display_value' => $valDisplay,
                    'spoken_value' => $this->formatSpokenValue($valDisplay, $updatedFieldDef, $language),
                    'label' => $updatedFieldDef['label'],
                    'type' => $updatedFieldDef['type'],
                    'events' => $updatedFieldDef['dynamic_events'] ?? ['input', 'change'],
                ];
                $otherUpdates = $merged;
            }
        }

        // Step E: If neither targeted, general deterministic, nor Gemini extraction could resolve the value:
        // KEEP current_field ACTIVE! Do NOT give generic apologies or lose state!
        if ($extracted === null) {
            $clarificationSpeech = $this->buildFieldClarificationSpeech($activeFieldDef, $language);

            return [
                'success' => false,
                'status' => 'clarify_field',
                'action' => 'prompt',
                'speech' => $clarificationSpeech,
                'form_key' => $formKey,
                'form_selector' => $schema['selector'],
                'guide_state' => $guideState, // retains active current_field!
                'active_field' => [
                    'key' => $currentFieldKey,
                    'name' => $activeFieldDef['name'],
                    'selector' => $activeFieldDef['selector'],
                    'label' => $activeFieldDef['label'],
                    'type' => $activeFieldDef['type'],
                ],
                'form_updates' => [],
            ];
        }

        // Step C & D: Succeeded! Build field updates
        $val = $extracted['value'];
        $valDisplay = $extracted['display_value'] ?? (string) $val;
        $isCorrection = $extracted['is_correction'] ?? false;
        $spokenVal = $this->formatSpokenValue($valDisplay, $updatedFieldDef, $language);

        $updates = !empty($otherUpdates) ? $otherUpdates : [
            $updatedFieldKey => [
                'field' => $updatedFieldKey,
                'name' => $updatedFieldDef['name'],
                'selector' => $updatedFieldDef['selector'],
                'value' => $val,
                'display_value' => $valDisplay,
                'spoken_value' => $spokenVal,
                'label' => $updatedFieldDef['label'],
                'type' => $updatedFieldDef['type'],
                'events' => $updatedFieldDef['dynamic_events'] ?? ['input', 'change'],
            ]
        ];

        // Update currentFields and filled_fields for ALL resolved updates
        $updatedKeys = array_keys($updates);
        foreach ($updates as $upKey => $upData) {
            $currentFields[$upKey] = $upData['value'];
        }
        $filledFields = array_unique(array_merge($guideState['filled_fields'] ?? [], $updatedKeys));
        $guideState['filled_fields'] = $filledFields;
        $handledFields = array_unique(array_merge($handledFields, $updatedKeys));
        $guideState['handled_fields'] = $handledFields;

        // Validate warnings (e.g. Staking limits)
        $warnings = [];
        if ($formKey === 'member_staking') {
            $packageVal = $currentFields['stak_package'] ?? null;
            $amountVal = isset($currentFields['amount']) ? (float) $currentFields['amount'] : null;
            if ($packageVal && $amountVal !== null) {
                $limitError = $this->validateStakingLimits($packageVal, $amountVal);
                if ($limitError !== null) {
                    $warnings[] = $limitError;
                }
            }
        }

        // Determine NEXT field in field_sequence past all updated keys
        $fieldSequence = $guideState['field_sequence'] ?? array_keys($schema['fields']);
        $nextFieldKey = $this->findNextEligibleField(
            $schema,
            $fieldSequence,
            $currentFields,
            $updatedKeys,
            $domEligibleKeys,
            $isEditForm,
            $handledFields
        );

        // If there is another field to ask:
        if ($nextFieldKey !== null) {
            $nextFieldDef = $schema['fields'][$nextFieldKey];
            $nextQuestion = $this->getFieldPromptQuestion($nextFieldDef, $schema, $language);

            // Step D: Confirm and move to next field naturally and politely
            if (count($updates) > 1) {
                $labelsWithVals = [];
                foreach ($updates as $u) {
                    $labelsWithVals[] = "{$u['label']}: {$u['display_value']}";
                }
                if ($language === 'en') {
                    $confirm = "Recorded " . implode(', ', $labelsWithVals) . ".";
                    $speech = "{$confirm} Now please specify the {$nextQuestion}";
                } elseif ($language === 'hinglish') {
                    $confirm = "Maine " . implode(', ', $labelsWithVals) . " bhar diya hai.";
                    $speech = "{$confirm} Ab {$nextQuestion}";
                } else {
                    $confirm = "मैंने " . implode(', ', $labelsWithVals) . " भर दिया है।";
                    $speech = "{$confirm} अब {$nextQuestion}";
                }
            } else {
                if ($language === 'en') {
                    if ($isCorrection) {
                        $confirm = "All right, {$updatedFieldDef['label']} has been changed to {$spokenVal}.";
                    } elseif ($updatedFieldDef['type'] === 'select' || $updatedFieldDef['type'] === 'radio') {
                        $confirm = "{$updatedFieldDef['label']} has been selected as {$spokenVal}.";
                    } elseif ($updatedFieldDef['type'] === 'checkbox') {
                        $confirm = "{$updatedFieldDef['label']} has been checked.";
                    } else {
                        $confirm = "Thank you, {$updatedFieldDef['label']} has been set to {$spokenVal}.";
                    }
                    $speech = "{$confirm} Now please specify the {$nextQuestion}";
                } elseif ($language === 'hinglish') {
                    if ($isCorrection) {
                        $confirm = "Theek hai, {$updatedFieldDef['label']} ko badal kar {$spokenVal} kar diya hai.";
                    } elseif ($updatedFieldDef['type'] === 'select' || $updatedFieldDef['type'] === 'radio') {
                        $confirm = "{$updatedFieldDef['label']} mein {$spokenVal} select kiya gaya hai.";
                    } elseif ($updatedFieldDef['type'] === 'checkbox') {
                        $confirm = "{$updatedFieldDef['label']} check kiya gaya hai.";
                    } else {
                        $confirm = "Dhanyavaad, {$updatedFieldDef['label']} mein {$spokenVal} bhar diya hai.";
                    }
                    $speech = "{$confirm} Ab {$nextQuestion}";
                } else {
                    // Hindi default
                    if ($isCorrection) {
                        $confirm = "ठीक है, {$updatedFieldDef['label']} को बदल कर {$spokenVal} कर दिया है।";
                    } elseif ($updatedFieldDef['type'] === 'select' || $updatedFieldDef['type'] === 'radio') {
                        $confirm = "{$updatedFieldDef['label']} में {$spokenVal} select किया गया है।";
                    } elseif ($updatedFieldDef['type'] === 'checkbox') {
                        $confirm = "{$updatedFieldDef['label']} check किया गया है।";
                    } else {
                        $confirm = "धन्यवाद, {$updatedFieldDef['label']} में {$spokenVal} भर दिया है।";
                    }
                    $speech = "{$confirm} अब {$nextQuestion}";
                }
            }
            if (!empty($warnings)) {
                $speech = implode(' ', $warnings) . ' ' . $speech;
            }

            $guideState['current_field'] = $nextFieldKey;
            $guideState['filled_fields'] = $filledFields;
            $guideState['handled_fields'] = $handledFields;
            $guideState['step_index'] = array_search($nextFieldKey, $fieldSequence, true) ?: 0;
            $guideState['status'] = 'in_progress';

            return [
                'success' => true,
                'status' => 'field_updated',
                'action' => 'prompt',
                'speech' => $speech,
                'form_key' => $formKey,
                'form_title' => $schema['title'],
                'form_selector' => $schema['selector'],
                'guide_state' => $guideState,
                'active_field' => [
                    'key' => $nextFieldKey,
                    'name' => $nextFieldDef['name'],
                    'selector' => $nextFieldDef['selector'],
                    'label' => $nextFieldDef['label'],
                    'type' => $nextFieldDef['type'],
                ],
                'form_updates' => array_values($updates),
                'warnings' => $warnings,
            ];
        }

        // Step F: All fields filled -> Complete session with summary of filled fields and clear instructions for manual entry
        return $this->buildCompletionPayload($schema, $currentFields, $guideState, $updates, $warnings, $updatedFieldDef, $valDisplay, $isCorrection);
    }

    /**
     * Phase 8: Resolve speech utterance specifically against the active field.
     */
    protected function extractTargetedFieldValue(
        string $rawSpeech,
        string $normSpeech,
        array $fieldDef,
        array $schema
    ): ?array {
        if ($this->isSensitiveField($fieldDef) || $this->isReadOnlyOrHiddenField($fieldDef) || ($fieldDef['file_upload'] ?? false)) {
            return null;
        }

        $type = $fieldDef['type'] ?? 'text';
        $name = strtolower($fieldDef['name'] ?? '');
        $label = strtolower($fieldDef['label'] ?? '');

        // 1. NUMBER FIELD
        if ($type === 'number' || str_contains($name, 'amount') || str_contains($label, 'amount')) {
            // Check for correction pattern: e.g. "nahi 2000 nahi 2500", "pehle 1000 tha ab 2000 kar do", "galat 2500 karo"
            $hasContrastKeywords = preg_match('/\b(nahi|not|galat|badal|change|balki|नहीं|pehle|ab|ki\s*jagah|instead\s*of)\b/iu', $rawSpeech);
            $normDigits = IraTextNormalizer::convertDevanagariDigits($rawSpeech);
            preg_match_all('/(?:\$|rs|inr|₹)?\s*([\d,]+(?:\.\d+)?)/i', $normDigits, $mAll);
            $hasMultipleNumbers = !empty($mAll[1]) && count($mAll[1]) >= 2;

            if ($hasContrastKeywords && $hasMultipleNumbers) {
                if ($this->formBridge->isConfigured()) {
                    return null;
                }
                $lastRaw = end($mAll[1]);
                $num = (float) str_replace(',', '', $lastRaw);
                return [
                    'value' => $num,
                    'display_value' => (string) $num,
                    'is_correction' => true,
                ];
            }

            // Normal numeric extraction with IraTextNormalizer (handles "एक हजार पांच सौ" -> 1500, "बारह हजार" -> 12000, "९८७६५" -> 98765)
            $parsedNum = IraTextNormalizer::normalizeNumericAmount($rawSpeech);
            if ($parsedNum !== null) {
                return [
                    'value' => $parsedNum,
                    'display_value' => (string) $parsedNum,
                ];
            }

            // Normal numeric extraction first (in case alias or currency was used)
            $extracted = $this->extractNumericValue($rawSpeech, $normSpeech, $fieldDef);
            if ($extracted !== null) {
                return $extracted;
            }

            // Solitary digits in speech: e.g. "1000", "500", "2500"
            $digitsText = IraTextNormalizer::convertDevanagariDigits($rawSpeech);
            if (preg_match('/(?:\$|rs|inr|₹)?\s*([\d,]+(?:\.\d+)?)/i', $digitsText, $m)) {
                $num = (float) str_replace(',', '', $m[1]);
                return [
                    'value' => $num,
                    'display_value' => (string) $num,
                ];
            }

            // Spoken Hindi numbers: "ek hazaar", "pachas", "do sau"
            $parsedHindi = $this->parseHindiSpokenNumber($normSpeech);
            if ($parsedHindi !== null) {
                return [
                    'value' => $parsedHindi,
                    'display_value' => (string) $parsedHindi,
                ];
            }

            return null;
        }

        // 2. PHONE / MOBILE FIELD
        if ($type === 'tel' || str_contains($name, 'mobile') || str_contains($name, 'phone') || str_contains($label, 'mobile') || str_contains($label, 'phone')) {
            $phone = IraTextNormalizer::normalizePhoneNumber($rawSpeech);
            if ($phone !== null) {
                return [
                    'value' => $phone,
                    'display_value' => $phone,
                ];
            }
        }

        // 3. SELECT / RADIO FIELD
        if ($type === 'select' || $type === 'radio') {
            $options = $fieldDef['options'] ?? [];
            if (empty($options)) return null;

            // Direct option matching (aliases, label, value)
            $optMatch = $this->extractOptionValue($rawSpeech, $normSpeech, $fieldDef);
            if ($optMatch !== null) {
                return $optMatch;
            }

            // If utterance has Devanagari (e.g. "कनाडा" or "भारत"), transliterate and match
            if (IraTextNormalizer::hasDevanagari($rawSpeech)) {
                $translit = IraTextNormalizer::transliterate($rawSpeech);
                $optMatch2 = $this->extractOptionValue($translit, $this->normalizeUtterance($translit), $fieldDef);
                if ($optMatch2 !== null) {
                    return $optMatch2;
                }
            }

            // Ordinal selection: "pehla", "dusra", "teesra", "first", "second"
            if (preg_match('/\b(pehla|pehli|first|1st|ek|one)\b/iu', $normSpeech) && isset($options[0])) {
                return ['value' => $options[0]['value'], 'display_value' => $options[0]['label']];
            }
            if (preg_match('/\b(dusra|dusri|second|2nd|do|two)\b/iu', $normSpeech) && isset($options[1])) {
                return ['value' => $options[1]['value'], 'display_value' => $options[1]['label']];
            }
            if (preg_match('/\b(teesra|teesri|third|3rd|teen|three)\b/iu', $normSpeech) && isset($options[2])) {
                return ['value' => $options[2]['value'], 'display_value' => $options[2]['label']];
            }
            if (preg_match('/\b(chautha|chauthi|fourth|4th|chaar|four)\b/iu', $normSpeech) && isset($options[3])) {
                return ['value' => $options[3]['value'], 'display_value' => $options[3]['label']];
            }

            return null;
        }

        // 4. CHECKBOX FIELD
        if ($type === 'checkbox') {
            if (preg_match('/\b(check|tick|agree|karo|haan|yes|on|true|theek|sahi|स्वीकार|मंजूर)\b/iu', $normSpeech)) {
                return ['value' => true, 'display_value' => 'Checked'];
            }
            if (preg_match('/\b(uncheck|untick|no|hatao|mat|false|nahi|नहीं)\b/iu', $normSpeech)) {
                return ['value' => false, 'display_value' => 'Unchecked'];
            }
            return null;
        }

        // 5. DATE FIELD
        if ($type === 'date') {
            return $this->extractDateValue($rawSpeech, $normSpeech, $fieldDef);
        }

        // 6. MEMBER ID / USER ID / SPONSOR ID / ALPHANUMERIC IDENTIFIERS
        if (IraTextNormalizer::isIdentifierField($fieldDef)) {
            $memId = $this->extractMemberId($normSpeech, $fieldDef);
            if ($memId !== null) {
                return [
                    'value' => $memId['value'],
                    'display_value' => $memId['display_value'],
                    'is_identifier' => true,
                ];
            }

            if (preg_match('/\b([A-Za-z]{2,4}\d{4,8})\b/', $rawSpeech, $mId)) {
                $id = strtoupper($mId[1]);
                return [
                    'value' => $id,
                    'display_value' => $id,
                    'is_identifier' => true,
                ];
            }

            $id = IraTextNormalizer::normalizeIdentifier($rawSpeech);
            if ($id !== null && !in_array(strtolower($id), ['cancel', 'stop', 'help', 'exit', 'next', 'back', 'form'])) {
                return [
                    'value' => $id,
                    'display_value' => $id,
                    'is_identifier' => true,
                ];
            }
            return null;
        }

        // 7. EMAIL FIELD
        if ($type === 'email' || str_contains($name, 'email')) {
            if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $rawSpeech, $m)) {
                return ['value' => $m[0], 'display_value' => $m[0]];
            }
        }

        // 8. NAME FIELD (Full Name, Account Holder Name, etc.)
        $isName = str_contains($name, 'name') || str_contains($label, 'name');
        if ($isName) {
            $cleanVal = preg_replace('/^(?:likho|daalo|bharo|naam|name|poora\s*naam|hai|yeh\s*hai|mera|meri|kripya|please)\s*(?:mein|me|ko|change\s*karke|badal\s*kar|to|as|[:=])?\s*/iu', '', $rawSpeech);
            $cleanVal = preg_replace('/\s*(?:likho|daalo|karo|bharo|kardo|kar\s*do|kar\s*de|kar\s*dijiye|kijiye|rakho|rakh\s*do|set\s*karo|set\s*kardo|set\s*kar\s*do|hoga|hai)$/iu', '', $cleanVal);
            $cleanVal = trim($cleanVal);
            if (mb_strlen($cleanVal) >= 1) {
                $romanName = IraTextNormalizer::transliterate($cleanVal, true);
                return [
                    'value' => $romanName,
                    'display_value' => $romanName,
                ];
            }
        }

        // 9. COUNTRY FIELD (if text input rather than select)
        if (str_contains($name, 'country') || str_contains($label, 'country')) {
            $cleanVal = preg_replace('/^(?:likho|daalo|bharo|country|desh|nation|videsh|hai|yeh\s*hai|mera|meri|kripya|please)\s*(?:mein|me|ko|change\s*karke|badal\s*kar|to|as|[:=])?\s*/iu', '', $rawSpeech);
            $cleanVal = preg_replace('/\s*(?:likho|daalo|karo|bharo|kardo|kar\s*do|kar\s*de|kar\s*dijiye|kijiye|rakho|rakh\s*do|set\s*karo|set\s*kardo|set\s*kar\s*do|hoga|hai)$/iu', '', $cleanVal);
            $cleanVal = trim($cleanVal);
            if (mb_strlen($cleanVal) >= 1) {
                $country = IraTextNormalizer::transliterate($cleanVal);
                return [
                    'value' => $country,
                    'display_value' => $country,
                ];
            }
        }

        // 10. GENERIC TEXT / TEXTAREA / MESSAGE
        $cleanVal = preg_replace('/^(?:likho|daalo|bharo|naam|hai|yeh\s*hai|mera|meri|kripya|please)\s*(?:mein|me|ko|change\s*karke|badal\s*kar|to|as|[:=])?\s*/iu', '', $rawSpeech);
        $cleanVal = preg_replace('/\s*(?:likho|daalo|karo|bharo|kardo|kar\s*do|kar\s*de|kar\s*dijiye|kijiye|rakho|rakh\s*do|set\s*karo|set\s*kardo|set\s*kar\s*do|hoga|hai)$/iu', '', $cleanVal);
        $cleanVal = trim($cleanVal);
        if (mb_strlen($cleanVal) >= 1) {
            $romanText = IraTextNormalizer::transliterate($cleanVal);
            return [
                'value' => $romanText,
                'display_value' => $romanText,
            ];
        }

        return null;
    }

    /**
     * Build targeted field clarification question when speech cannot be resolved.
     * Retains active field context politely and asks specific clarification without generic apologies.
     */
    protected function buildFieldClarificationSpeech(array $fieldDef, string $lang = 'hi'): string
    {
        $label = $fieldDef['label'];
        $type = $fieldDef['type'] ?? 'text';

        // 1. Identifier fields (Member ID, Sponsor ID, User ID)
        if (IraTextNormalizer::isIdentifierField($fieldDef)) {
            if ($lang === 'en') {
                return "Sorry, I could not understand the {$label}. Please speak it once more—you can say each letter and digit individually.";
            } elseif ($lang === 'hinglish') {
                return "Maaf kijiye, main {$label} theek se samajh nahi paayi. Kripya use ek baar phir boliye—letters aur digits alag-alag bol sakte hain.";
            }
            return "माफ़ कीजिए, मैं {$label} ठीक से समझ नहीं पाई। कृपया उसे एक बार फिर बोलिए—अक्षर और अंक अलग-अलग बोल सकते हैं।";
        }

        // 2. Select / Radio options
        if ($type === 'select' || $type === 'radio') {
            $options = $fieldDef['options'] ?? [];
            if (!empty($options)) {
                $optLabels = array_slice(array_map(fn($o) => $o['label'], $options), 0, 4);
                $optStr = implode(', ', $optLabels);
                if ($lang === 'en') {
                    return "Sorry, I didn't catch that. Available options for {$label} are: {$optStr}. Which one would you prefer?";
                } elseif ($lang === 'hinglish') {
                    return "Maaf kijiye, main {$label} ka option samajh nahi paayi. Available options hain: {$optStr}. Kripya inmein se ek option batayein.";
                }
                return "माफ़ कीजिए, मैं {$label} का विकल्प समझ नहीं पाई। उपलब्ध विकल्प हैं: {$optStr}। कृपया इनमें से एक विकल्प बताइए।";
            }
            if ($lang === 'en') {
                return "Sorry, I didn't catch that. Please specify the option for {$label} again.";
            } elseif ($lang === 'hinglish') {
                return "Maaf kijiye, main {$label} samajh nahi paayi. Kripya option dobara batayein.";
            }
            return "माफ़ कीजिए, मैं {$label} समझ नहीं पाई। कृपया विकल्प दोबारा बताइए।";
        }

        // 3. Amount / Number
        if ($type === 'number' || str_contains(strtolower($fieldDef['name'] ?? ''), 'amount')) {
            if ($lang === 'en') {
                return "Sorry, I could not understand the amount for {$label}. Please specify the amount again.";
            } elseif ($lang === 'hinglish') {
                return "Maaf kijiye, main {$label} ka amount theek se samajh nahi paayi. Kripya amount dobara boliye.";
            }
            return "माफ़ कीजिए, मैं {$label} की value समझ नहीं पाई। कृपया {$label} दोबारा बताइए।";
        }

        if ($lang === 'en') {
            return "Sorry, I could not understand the {$label}. Please repeat it.";
        } elseif ($lang === 'hinglish') {
            return "Maaf kijiye, main {$label} theek se samajh nahi paayi. Kripya dobara boliye.";
        }
        return "माफ़ कीजिए, मैं {$label} ठीक से समझ नहीं पाई। कृपया दोबारा बोलिए।";
    }

    /**
     * Format a value for digit-by-digit or natural voice read-back.
     */
    public function formatSpokenValue(string $val, array $fieldDef, string $lang = 'hi'): string
    {
        $clean = trim($val);
        if ($clean === '') return '';

        $type = $fieldDef['type'] ?? 'text';
        $name = strtolower($fieldDef['name'] ?? '');
        $label = strtolower($fieldDef['label'] ?? '');

        $isIdentifier = IraTextNormalizer::isIdentifierField($fieldDef);
        $isPhone = $type === 'tel' || str_contains($name, 'phone') || str_contains($name, 'mobile') || str_contains($label, 'mobile');

        if ($isIdentifier || $isPhone) {
            return IraTextNormalizer::formatDigitByDigitSpeech($clean, $lang);
        }

        return $clean;
    }

    /**
     * Phase 8: Build completion payload summarizing filled fields and manual entry reminders.
     */
    protected function buildCompletionPayload(
        array $schema,
        array $currentFields,
        array $guideState,
        array $updates = [],
        array $warnings = [],
        ?array $lastFieldDef = null,
        ?string $lastValDisplay = null,
        bool $isCorrection = false
    ): array {
        $guideState['current_field'] = null;
        $guideState['status'] = 'completed';
        $lang = $guideState['language'] ?? 'hi';

        if ($lastFieldDef && $lastValDisplay !== null) {
            $spokenVal = $this->formatSpokenValue($lastValDisplay, $lastFieldDef, $lang);
            if ($isCorrection) {
                $confirm = "ठीक है, {$lastFieldDef['label']} को बदल कर {$spokenVal} कर दिया है। ";
            } elseif ($lastFieldDef['type'] === 'select' || $lastFieldDef['type'] === 'radio') {
                $confirm = "{$lastFieldDef['label']} में {$spokenVal} select किया गया है। ";
            } elseif ($lastFieldDef['type'] === 'checkbox') {
                $confirm = "{$lastFieldDef['label']} check किया गया है। ";
            } else {
                $confirm = "धन्यवाद, {$lastFieldDef['label']} में {$spokenVal} भर दिया है। ";
            }
        } else {
            $confirm = "";
        }

        $lead = "Form के सभी आवश्यक fields भर दिए गए हैं। ";
        if ($lang === 'en') {
            $compMsg = "{$lead}The information you provided has been filled into the relevant fields. Please review the values and submit the form manually when ready.";
        } elseif ($lang === 'hinglish') {
            $compMsg = "{$lead}Aapne jo jaankari di thi, woh sambandhit fields mein bhar di hai. Kripya values ek baar dekh lijiye aur form sahi hone par swayam submit kijiye.";
        } else {
            $compMsg = "{$lead}आपने जो जानकारी दी थी, वह संबंधित fields में भर दी है। कृपया values एक बार देख लीजिए और form सही होने पर स्वयं submit कीजिए।";
        }

        $speech = "{$confirm}{$compMsg}";

        // Sensitive field instruction
        $sensitiveFields = array_filter($schema['fields'], fn($f) => $this->isSensitiveField($f));
        if (!empty($sensitiveFields)) {
            $sensLabels = $this->formatFieldLabelsList(array_map(fn($f) => $f['label'], $sensitiveFields));
            if ($lang === 'en') {
                $speech .= " For security, please enter your {$sensLabels} manually on screen.";
            } elseif ($lang === 'hinglish') {
                $speech .= " Suraksha ke liye, kripya apna {$sensLabels} screen par manually enter karein.";
            } else {
                $speech .= " सुरक्षा के लिए, कृपया अपना {$sensLabels} स्क्रीन पर manually enter करें।";
            }
        }

        // File upload instruction
        $fileFields = array_filter($schema['fields'], fn($f) => ($f['file_upload'] ?? false) === true);
        if (!empty($fileFields)) {
            $fileLabels = $this->formatFieldLabelsList(array_map(fn($f) => $f['label'], $fileFields));
            $speech .= " कृपया स्क्रीन पर Choose File बटन से {$fileLabels} अपलोड करें।";
        }

        $speech .= " विवरण check करने के बाद आप Submit बटन पर click कर सकते हैं।";

        if (!empty($warnings)) {
            $speech = implode(' ', $warnings) . ' ' . $speech;
        }

        return [
            'success' => true,
            'status' => 'completed',
            'action' => 'complete',
            'speech' => $speech,
            'form_key' => $schema['key'],
            'form_title' => $schema['title'],
            'form_selector' => $schema['selector'],
            'guide_state' => $guideState,
            'active_field' => null,
            'form_updates' => array_values($updates),
            'warnings' => $warnings,
        ];
    }

    /**
     * Phase 8: Format human-readable list of field labels.
     */
    public function formatFieldLabelsList(array $labels): string
    {
        $labels = array_values($labels);
        $count = count($labels);
        if ($count === 0) return '';
        if ($count === 1) return $labels[0];
        if ($count === 2) return "{$labels[0]} और {$labels[1]}";
        if ($count === 3) return "{$labels[0]}, {$labels[1]} और {$labels[2]}";

        return "{$labels[0]}, {$labels[1]} और बाकी उपलब्ध fields";
    }

    /**
     * Phase 8: Generate prompt question for a specific field with multi-language support.
     */
    public function getFieldPromptQuestion(array $fieldDef, array $schema, string $lang = 'hi'): string
    {
        $label = $fieldDef['label'];
        $type = $fieldDef['type'] ?? 'text';
        $name = strtolower($fieldDef['name'] ?? '');
        $labelLower = strtolower($label);

        // Security Shield: Sensitive fields must never be asked for spoken entry
        if ($this->isSensitiveField($fieldDef)) {
            if ($lang === 'en') {
                return "Please enter your {$label} manually on screen for security.";
            } elseif ($lang === 'hinglish') {
                return "Suraksha ke liye kripya apna {$label} screen par manually enter karein.";
            }
            return "सुरक्षा के लिए कृपया अपना {$label} स्क्रीन पर manually भरें।";
        }

        // 1. SELECT / RADIO
        if ($type === 'select' || $type === 'radio') {
            $options = $fieldDef['options'] ?? [];
            if (!empty($options)) {
                $optLabels = array_slice(array_map(fn($o) => $o['label'], $options), 0, 4);
                $optStr = implode(', ', $optLabels);
                if ($lang === 'en') {
                    return "Which option would you like for {$label}? Available options are: {$optStr}.";
                } elseif ($lang === 'hinglish') {
                    return "{$label} mein kaun sa option select karna hai? Available options hain: {$optStr}.";
                }
                return "{$label} में कौन सा विकल्प चुनना है? उपलब्ध विकल्प हैं: {$optStr}।";
            }
            if ($lang === 'en') {
                return "Which option would you like for {$label}?";
            } elseif ($lang === 'hinglish') {
                return "{$label} mein kaun sa option select karna hai?";
            }
            return "{$label} में कौन सा विकल्प चुनना है?";
        }

        // 2. IDENTIFIERS (Member ID, Sponsor ID, User ID)
        if (IraTextNormalizer::isIdentifierField($fieldDef)) {
            if ($lang === 'en') {
                return "Please specify the {$label}—you can speak letters and digits individually.";
            } elseif ($lang === 'hinglish') {
                return "{$label} batayein—letters aur digits alag-alag bol sakte hain.";
            }
            return "{$label} बताइए—अक्षर और अंक अलग-अलग बोल सकते हैं।";
        }

        // 3. NAME FIELD
        if (str_contains($name, 'name') || str_contains($labelLower, 'name')) {
            if ($lang === 'en') {
                return "What is your {$label}?";
            } elseif ($lang === 'hinglish') {
                return "Aapka poora {$label} kya hai?";
            }
            return "आपका पूरा नाम क्या है?";
        }

        // 4. PHONE / MOBILE
        if (str_contains($name, 'mobile') || str_contains($name, 'phone') || str_contains($labelLower, 'mobile') || str_contains($labelLower, 'phone') || $type === 'tel') {
            if ($lang === 'en') {
                return "What is your {$label}?";
            } elseif ($lang === 'hinglish') {
                return "{$label} batayein?";
            }
            return "{$label} बताइए?";
        }

        // 5. COUNTRY
        if (str_contains($name, 'country') || str_contains($labelLower, 'country')) {
            if ($lang === 'en') {
                return "Which country should be selected for {$label}?";
            } elseif ($lang === 'hinglish') {
                return "{$label} kya rakhni hai?";
            }
            return "{$label} क्या रखनी है?";
        }

        // 6. MESSAGE / REMARK / COMMENT
        if (str_contains($name, 'message') || str_contains($name, 'remark') || str_contains($name, 'comment') || str_contains($name, 'reason') || str_contains($labelLower, 'message') || str_contains($labelLower, 'remark') || str_contains($labelLower, 'comment')) {
            if ($lang === 'en') {
                return "What should be written in {$label}?";
            } elseif ($lang === 'hinglish') {
                return "{$label} mein kya likhna hai?";
            }
            return "{$label} में क्या लिखना है?";
        }

        // 7. NUMBER / AMOUNT
        if ($type === 'number' || str_contains($name, 'amount') || str_contains($labelLower, 'amount')) {
            if ($lang === 'en') {
                return "What is the amount for {$label}?";
            } elseif ($lang === 'hinglish') {
                return "{$label} mein kitna amount bharna hai?";
            }
            return "{$label} में कितनी value भरनी है?";
        }

        // 8. CHECKBOX
        if ($type === 'checkbox') {
            if ($lang === 'en') {
                return "Would you like to accept {$label}?";
            } elseif ($lang === 'hinglish') {
                return "Kya aap {$label} accept karna chahte hain?";
            }
            return "क्या आप {$label} को स्वीकार या चेक करना चाहते हैं?";
        }

        // 9. DATE
        if ($type === 'date') {
            if ($lang === 'en') {
                return "Please specify the date for {$label} (e.g., 2026-10-24).";
            } elseif ($lang === 'hinglish') {
                return "{$label} ki date batayein (jaise: 2026-10-24)?";
            }
            return "{$label} की तारीख बताइए (जैसे: 2026-10-24)?";
        }

        if ($lang === 'en') {
            return "Please specify {$label}?";
        } elseif ($lang === 'hinglish') {
            return "{$label} batayein?";
        }
        return "{$label} बताइए?";
    }

    /**
     * Phase 8: Handle Review / Summary in guided mode.
     */
    protected function handleGuidedReviewCommand(array $schema, array $currentFields, array $guideState): array
    {
        $phrases = [];
        foreach ($schema['fields'] as $k => $f) {
            if (!$this->isSensitiveField($f) && !$this->isReadOnlyOrHiddenField($f) && !($f['file_upload'] ?? false)) {
                $val = $currentFields[$k] ?? null;
                if ($val !== null && trim((string) $val) !== '') {
                    $phrases[] = "{$f['label']}: {$val}";
                }
            }
        }

        if (empty($phrases)) {
            $summaryText = "Abhi tak koi field nahi bhara gaya hai.";
        } else {
            $summaryText = "Abhi tak bhare gaye details hain: " . implode(', ', $phrases) . "।";
        }

        $currentFieldKey = $guideState['current_field'] ?? null;
        $activeFieldDef = $currentFieldKey ? ($schema['fields'][$currentFieldKey] ?? null) : null;

        if ($activeFieldDef) {
            $nextPrompt = $this->getFieldPromptQuestion($activeFieldDef, $schema);
            $speech = "{$summaryText} Abhi {$activeFieldDef['label']} bharna baaki hai. {$nextPrompt}";
            $action = 'prompt';
        } else {
            $speech = "{$summaryText} Sabhi zaroori fields bhar diye gaye hain. Kripya screen par check karein.";
            $action = 'complete';
        }

        return [
            'success' => true,
            'status' => 'review',
            'action' => $action,
            'speech' => $speech,
            'form_key' => $schema['key'],
            'form_selector' => $schema['selector'],
            'guide_state' => $guideState,
            'active_field' => $activeFieldDef ? [
                'key' => $currentFieldKey,
                'name' => $activeFieldDef['name'],
                'selector' => $activeFieldDef['selector'],
                'label' => $activeFieldDef['label'],
                'type' => $activeFieldDef['type'],
            ] : null,
            'form_updates' => [],
        ];
    }

    /**
     * Phase 8: Handle Remaining fields query in guided mode.
     */
    protected function handleGuidedRemainingQuery(array $schema, array $currentFields, array $guideState): array
    {
        $domEligible = $guideState['dom_eligible_fields'] ?? null;
        $remaining = $this->calculateRemainingRequired($schema, $currentFields, $domEligible);
        $currentFieldKey = $guideState['current_field'] ?? null;
        $activeFieldDef = $currentFieldKey ? ($schema['fields'][$currentFieldKey] ?? null) : null;

        if (empty($remaining)) {
            $speech = "Is form ke sabhi zaroori fields bhare hue hain. Aap check karke submit kar sakte hain.";
            $action = 'complete';
        } else {
            $fieldsList = implode(', ', $remaining);
            $speech = "Is form mein abhi yeh fields bharna baaki hai: {$fieldsList}।";
            if ($activeFieldDef) {
                $speech .= " " . $this->getFieldPromptQuestion($activeFieldDef, $schema);
            }
            $action = 'prompt';
        }

        return [
            'success' => true,
            'status' => 'status_query',
            'action' => $action,
            'speech' => $speech,
            'form_key' => $schema['key'],
            'form_selector' => $schema['selector'],
            'guide_state' => $guideState,
            'active_field' => $activeFieldDef ? [
                'key' => $currentFieldKey,
                'name' => $activeFieldDef['name'],
                'selector' => $activeFieldDef['selector'],
                'label' => $activeFieldDef['label'],
                'type' => $activeFieldDef['type'],
            ] : null,
            'form_updates' => [],
            'remaining_required' => $remaining,
        ];
    }

    /**
     * Process a form filling turn.
     *
     * @param string $speech Raw transcribed utterance
     * @param string $formKey Registered form key
     * @param string $role Server-authoritative user role
     * @param array $currentFields Currently populated values from the DOM
     * @param array $context Conversational context
     * @return array
     */
    public function processFormTurn(
        string $speech,
        string $formKey,
        string $role = 'guest',
        array $currentFields = [],
        array $context = []
    ): array {
        $clean = trim($speech);
        if ($clean === '') {
            return [
                'success' => false,
                'status' => 'empty',
                'speech' => 'Kripya boliye ki aap form mein kya bharna chahte hain.',
                'form_updates' => [],
                'remaining_required' => [],
            ];
        }

        $schema = IraFormRegistry::getForm($formKey);
        if (!$schema) {
            return [
                'success' => false,
                'status' => 'unregistered_form',
                'speech' => 'Is page par koi registered form nahi mila.',
                'form_updates' => [],
                'remaining_required' => [],
            ];
        }

        // Authoritative role verification
        if (!IraFormRegistry::isFormAuthorized($formKey, $role)) {
            return [
                'success' => false,
                'status' => 'unauthorized',
                'speech' => 'Aapko is form ko bharne ki anumati nahi hai.',
                'form_updates' => [],
                'remaining_required' => [],
            ];
        }

        $normalized = $this->normalizeUtterance($clean);

        // 1. Check for "What is left to fill?" query
        if ($this->isRemainingFieldsQuery($normalized)) {
            return $this->handleRemainingFieldsQuery($schema, $currentFields);
        }

        // 2. Check for "Clear / Reset Form" command
        if ($this->isResetFormCommand($normalized)) {
            return $this->handleResetFormCommand($schema);
        }

        // 3. Extract field updates deterministically
        $extractionResult = $this->extractFieldsDeterministic($clean, $schema, $currentFields);

        $updates = $extractionResult['updates'];
        $sensitiveSkipped = $extractionResult['sensitive_skipped'];
        $readOnlySkipped = $extractionResult['read_only_skipped'];
        $fileSkipped = $extractionResult['file_skipped'];
        $warnings = $extractionResult['warnings'];

        // If deterministic extraction found no updates, try LLM fallback if configured
        if (empty($updates) && empty($sensitiveSkipped) && empty($fileSkipped) && $this->formBridge->isConfigured()) {
            $llmResult = $this->extractWithGemini($clean, $schema, $currentFields);
            if (!empty($llmResult['updates'])) {
                $updates = $llmResult['updates'];
                $sensitiveSkipped = array_merge($sensitiveSkipped, $llmResult['sensitive_skipped'] ?? []);
            }
        }

        if (empty($updates)) {
            if (!empty($sensitiveSkipped)) {
                return [
                    'success' => true,
                    'status' => 'sensitive_field_blocked',
                    'speech' => 'Suraksha ke liye, kripya apna password ya transaction PIN screen par manually enter karein.',
                    'form_key' => $formKey,
                    'form_selector' => $schema['selector'],
                    'form_updates' => [],
                    'sensitive_skipped' => $sensitiveSkipped,
                    'remaining_required' => $this->calculateRemainingRequired($schema, $currentFields),
                ];
            }

            if (!empty($fileSkipped)) {
                return [
                    'success' => true,
                    'status' => 'file_input_blocked',
                    'speech' => 'File ya photo upload karne ke liye, kripya screen par Choose File button par click karein.',
                    'form_key' => $formKey,
                    'form_selector' => $schema['selector'],
                    'form_updates' => [],
                    'remaining_required' => $this->calculateRemainingRequired($schema, $currentFields),
                ];
            }

            if (!empty($readOnlySkipped)) {
                $readOnlyNames = implode(', ', $readOnlySkipped);
                return [
                    'success' => true,
                    'status' => 'readonly_field',
                    'speech' => "Yeh field ({$readOnlyNames}) read-only hai aur isse badla nahi jaa sakta.",
                    'form_key' => $formKey,
                    'form_selector' => $schema['selector'],
                    'form_updates' => [],
                    'remaining_required' => $this->calculateRemainingRequired($schema, $currentFields),
                ];
            }

            return [
                'success' => false,
                'status' => 'no_match',
                'speech' => 'Main samajh nahi paayi ki aap kaunsa field bharna chahte hain. Kripya field ka naam aur value batayein, jaise "Amount 1000" ya "Package Gold".',
                'form_key' => $formKey,
                'form_selector' => $schema['selector'],
                'form_updates' => [],
                'remaining_required' => $this->calculateRemainingRequired($schema, $currentFields),
            ];
        }

        // Cross-validate Staking package limits if staking form
        if ($formKey === 'member_staking') {
            $packageVal = $updates['stak_package']['value'] ?? ($currentFields['stak_package'] ?? null);
            $amountVal = isset($updates['amount']) ? (float) $updates['amount']['value'] : (isset($currentFields['amount']) ? (float) $currentFields['amount'] : null);

            if ($packageVal && $amountVal !== null) {
                $limitError = $this->validateStakingLimits($packageVal, $amountVal);
                if ($limitError !== null) {
                    $warnings[] = $limitError;
                }
            }
        }

        // Build human-friendly spoken confirmation
        $speechResponse = $this->buildSpeechConfirmation($updates, $sensitiveSkipped, $warnings, $schema, $currentFields);

        // Calculate remaining required fields after applying updates
        $mergedFields = array_merge($currentFields, array_map(fn($u) => $u['value'], $updates));
        $remaining = $this->calculateRemainingRequired($schema, $mergedFields);

        return [
            'success' => true,
            'status' => 'updated',
            'form_key' => $formKey,
            'form_title' => $schema['title'],
            'form_selector' => $schema['selector'],
            'form_updates' => array_values($updates),
            'speech' => $speechResponse,
            'speech_language' => 'hinglish',
            'sensitive_skipped' => $sensitiveSkipped,
            'warnings' => $warnings,
            'remaining_required' => $remaining,
        ];
    }

    /**
     * Deterministic Field Extraction Engine.
     * Analyzes utterance for known field aliases, numeric amounts, member IDs, options, and dates.
     */
    protected function extractFieldsDeterministic(string $rawSpeech, array $schema, array $currentFields): array
    {
        $speech = trim($rawSpeech);
        $norm = $this->normalizeUtterance($speech);

        $updates = [];
        $sensitiveSkipped = [];
        $readOnlySkipped = [];
        $fileSkipped = [];
        $warnings = [];

        // Check each registered field
        foreach ($schema['fields'] as $fieldKey => $fieldDef) {
            // Check if user is referencing a sensitive field (password, PIN)
            if ($fieldDef['sensitive'] ?? false) {
                if ($this->isFieldMentioned($norm, $fieldDef)) {
                    $sensitiveSkipped[] = $fieldDef['label'];
                }
                continue;
            }

            // Check if user is referencing a file upload field
            if ($fieldDef['file_upload'] ?? false) {
                if ($this->isFieldMentioned($norm, $fieldDef)) {
                    $fileSkipped[] = $fieldDef['label'];
                }
                continue;
            }

            // Check if field is read-only
            if ($fieldDef['read_only'] ?? false) {
                if ($this->isFieldMentioned($norm, $fieldDef)) {
                    $readOnlySkipped[] = $fieldDef['label'];
                }
                continue;
            }

            // Extract value based on field type
            $extractedValue = null;

            switch ($fieldDef['type']) {
                case 'select':
                case 'radio':
                    $extractedValue = $this->extractOptionValue($speech, $norm, $fieldDef);
                    break;

                case 'checkbox':
                    $extractedValue = $this->extractCheckboxValue($speech, $norm, $fieldDef);
                    break;

                case 'number':
                    $extractedValue = $this->extractNumericValue($speech, $norm, $fieldDef);
                    break;

                case 'date':
                    $extractedValue = $this->extractDateValue($speech, $norm, $fieldDef);
                    break;

                case 'text':
                case 'email':
                case 'textarea':
                default:
                    $extractedValue = $this->extractTextualValue($speech, $norm, $fieldDef);
                    break;
            }

            if ($extractedValue !== null) {
                $updates[$fieldKey] = [
                    'field' => $fieldKey,
                    'name' => $fieldDef['name'],
                    'selector' => $fieldDef['selector'],
                    'value' => $extractedValue['value'],
                    'display_value' => $extractedValue['display_value'] ?? (string) $extractedValue['value'],
                    'label' => $fieldDef['label'],
                    'type' => $fieldDef['type'],
                    'events' => $fieldDef['dynamic_events'] ?? ['input', 'change'],
                ];
            }
        }

        return [
            'updates' => $updates,
            'sensitive_skipped' => array_unique($sensitiveSkipped),
            'read_only_skipped' => array_unique($readOnlySkipped),
            'file_skipped' => array_unique($fileSkipped),
            'warnings' => $warnings,
        ];
    }

    /**
     * Check if a field's aliases appear in the utterance.
     */
    protected function isFieldMentioned(string $normSpeech, array $fieldDef): bool
    {
        $aliases = $fieldDef['aliases'] ?? [];
        $aliases[] = $fieldDef['name'];
        $aliases[] = $fieldDef['label'];

        foreach ($aliases as $alias) {
            $cleanAlias = $this->normalizeUtterance($alias);
            if ($cleanAlias === '') continue;

            if (preg_match('/(^|\b)' . preg_quote($cleanAlias, '/') . '(\b|$)/iu', $normSpeech)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract select or radio option value.
     */
    protected function extractOptionValue(string $rawSpeech, string $normSpeech, array $fieldDef): ?array
    {
        if (empty($fieldDef['options'])) {
            return null;
        }

        // 1. Direct option match in speech
        foreach ($fieldDef['options'] as $opt) {
            $value = $opt['value'];
            $label = $opt['label'];
            $aliases = $opt['aliases'] ?? [];
            $aliases[] = $value;
            $aliases[] = $label;

            foreach ($aliases as $alias) {
                $cleanAlias = $this->normalizeUtterance($alias);
                if ($cleanAlias === '') continue;

                // Match whole word alias
                if (preg_match('/(^|\b)' . preg_quote($cleanAlias, '/') . '(\b|$)/iu', $normSpeech)) {
                    return [
                        'value' => $value,
                        'display_value' => $label,
                    ];
                }
            }
        }

        // 2. Transliterated match if Devanagari present
        if (IraTextNormalizer::hasDevanagari($rawSpeech)) {
            $translit = IraTextNormalizer::transliterate($rawSpeech);
            $normTranslit = $this->normalizeUtterance($translit);
            foreach ($fieldDef['options'] as $opt) {
                $value = $opt['value'];
                $label = $opt['label'];
                $aliases = $opt['aliases'] ?? [];
                $aliases[] = $value;
                $aliases[] = $label;

                foreach ($aliases as $alias) {
                    $cleanAlias = $this->normalizeUtterance($alias);
                    if ($cleanAlias === '') continue;

                    if (preg_match('/(^|\b)' . preg_quote($cleanAlias, '/') . '(\b|$)/iu', $normTranslit)) {
                        return [
                            'value' => $value,
                            'display_value' => $label,
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Extract checkbox boolean value.
     */
    protected function extractCheckboxValue(string $rawSpeech, string $normSpeech, array $fieldDef): ?array
    {
        if (!$this->isFieldMentioned($normSpeech, $fieldDef)) {
            return null;
        }

        // Positive triggers: "haan", "check", "agree", "tick", "karo", "yes"
        if (preg_match('/\b(check|tick|agree|karo|haan|yes|on|true|स्वीकार)\b/iu', $normSpeech)) {
            return ['value' => true, 'display_value' => 'Checked'];
        }

        // Negative triggers: "uncheck", "mat karo", "no", "hatao"
        if (preg_match('/\b(uncheck|untick|no|hatao|mat|false)\b/iu', $normSpeech)) {
            return ['value' => false, 'display_value' => 'Unchecked'];
        }

        return ['value' => true, 'display_value' => 'Checked'];
    }

    /**
     * Extract numeric value (amount, rank, rate).
     * Prioritizes explicit digit literals and currency symbols before spoken Hindi words.
     */
    protected function extractNumericValue(string $rawSpeech, string $normSpeech, array $fieldDef): ?array
    {
        // When multiple numbers and contrast keywords are present and Gemini is configured, delegate to Gemini
        $hasContrast = preg_match('/\b(nahi|not|galat|badal|change|balki|नहीं|pehle|ab|ki\s*jagah|instead\s*of)\b/iu', $rawSpeech);
        $normDigits = IraTextNormalizer::convertDevanagariDigits($rawSpeech);
        preg_match_all('/(?:\$|rs|inr|₹)?\s*([\d,]+(?:\.\d+)?)/i', $normDigits, $mAll);
        if ($hasContrast && !empty($mAll[1]) && count($mAll[1]) >= 2 && $this->formBridge->isConfigured()) {
            return null;
        }

        $isFieldNamed = $this->isFieldMentioned($normSpeech, $fieldDef);

        // Pattern 1: Standalone or field-adjacent currency phrase e.g. "500 dollars", "$1000", "1500 rs", "100 rupaye", "५०० डॉलर", "500 transfer"
        if (preg_match('/(\$?\s*[\d,]+(?:\.\d+)?)\s*(?:dollars?|dollar|usd|\$|rupaye|rs|inr|transfer|bhejna|bhejo|send|रुपये|रुपया|डॉलर|ट्रांसफर)\b/iu', $normSpeech, $m)) {
            $num = (float) str_replace(['$', ',', ' '], '', $m[1]);
            return ['value' => $num, 'display_value' => (string) $num];
        }

        // Pattern 2: Explicit field mention followed by digits e.g. "amount 1500", "amount mein 500", "rashi 2000"
        $fieldPatterns = [];
        foreach ($fieldDef['aliases'] as $a) {
            $fieldPatterns[] = preg_quote($this->normalizeUtterance($a), '/');
        }
        $aliasRegex = implode('|', $fieldPatterns);

        if ($aliasRegex !== '') {
            if (preg_match('/(?:' . $aliasRegex . ')\s*(?:mein|me|ko|ka|ki|ke|liye|equal|to|hai|h)?\s*[:=]?\s*(\$?\s*[\d,]+(?:\.\d+)?)/iu', $normSpeech, $m)) {
                $num = (float) str_replace(['$', ',', ' '], '', $m[1]);
                return ['value' => $num, 'display_value' => (string) $num];
            }
        }

        // Pattern 3: If field is explicitly mentioned and there is a solitary number in speech
        if ($isFieldNamed && preg_match('/\b(\d+(?:\.\d+)?)\b/', $normSpeech, $m)) {
            $num = (float) $m[1];
            return ['value' => $num, 'display_value' => (string) $num];
        }

        // Pattern 4: Spoken Hindi words after field name: "amount do hazaar", "amount pachas"
        if ($aliasRegex !== '') {
            if (preg_match('/(?:' . $aliasRegex . ')\s*(?:mein|me|ko|hai|h)?\s+([a-z\s\x{0900}-\x{097F}]+?)(?:\s*(?:karo|rakho|daalo|bharo|kar\s*do|bhar\s*do|daal\s*do|rakh\s*do|bhej\s*do|aur|\.|$))/iu', $normSpeech, $m)) {
                $hindiWords = trim($m[1]);
                $parsed = $this->parseHindiSpokenNumber($hindiWords);
                if ($parsed !== null) {
                    return ['value' => $parsed, 'display_value' => (string) $parsed];
                }
            }
        }

        // Pattern 5: If field is explicitly mentioned with spoken Hindi words anywhere in speech
        if ($isFieldNamed) {
            $parsed = $this->parseHindiSpokenNumber($normSpeech);
            if ($parsed !== null) {
                return ['value' => $parsed, 'display_value' => (string) $parsed];
            }
        }

        return null;
    }

    /**
     * Extract date value (YYYY-MM-DD) from speech.
     */
    protected function extractDateValue(string $rawSpeech, string $normSpeech, array $fieldDef): ?array
    {
        $isFieldNamed = $this->isFieldMentioned($normSpeech, $fieldDef);

        // Date formats: YYYY-MM-DD or DD-MM-YYYY or DD/MM/YYYY
        if (preg_match('/\b(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})\b/', $normSpeech, $m)) {
            $date = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
            return ['value' => $date, 'display_value' => $date];
        }
        if (preg_match('/\b(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})\b/', $normSpeech, $m)) {
            $date = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            return ['value' => $date, 'display_value' => $date];
        }

        // Natural month format: "1 October", "24 October 2026", "1st Oct"
        $months = [
            'january' => '01', 'jan' => '01',
            'february' => '02', 'feb' => '02',
            'march' => '03', 'mar' => '03',
            'april' => '04', 'apr' => '04',
            'may' => '05',
            'june' => '06', 'jun' => '06',
            'july' => '07', 'jul' => '07',
            'august' => '08', 'aug' => '08',
            'september' => '09', 'sep' => '09', 'sept' => '09',
            'october' => '10', 'oct' => '10',
            'november' => '11', 'nov' => '11',
            'december' => '12', 'dec' => '12',
        ];

        $monthPattern = implode('|', array_keys($months));

        // "from date 1 october" or "to date 24 october"
        if ($isFieldNamed) {
            if (preg_match('/(?:' . implode('|', array_map(fn($a) => preg_quote($this->normalizeUtterance($a), '/'), $fieldDef['aliases'])) . ')\s*(?:mein|me|ko|hai)?\s*(\d{1,2})(?:st|nd|rd|th)?\s+(' . $monthPattern . ')(?:\s+(\d{4}))?/iu', $normSpeech, $m)) {
                $day = (int) $m[1];
                $month = $months[strtolower($m[2])];
                $year = !empty($m[3]) ? (int) $m[3] : (int) date('Y');
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                return ['value' => $date, 'display_value' => $date];
            }
        }

        return null;
    }

    /**
     * Extract textual value (memberid, name, email, subject, issue, etc.).
     */
    protected function extractTextualValue(string $rawSpeech, string $normSpeech, array $fieldDef): ?array
    {
        $fieldName = $fieldDef['name'];

        // Special case: Member ID (e.g. GAB10025, gab123456)
        if ($fieldName === 'memberid' || $fieldName === 'sponsor' || $fieldName === 'searchid') {
            return $this->extractMemberId($normSpeech, $fieldDef);
        }

        // Special case: Wallet Address (e.g. USDT TRC20 / BEP20)
        if ($fieldName === 'wallet_address' || $fieldName === 'address') {
            if (preg_match('/\b(0x[a-fA-F0-9]{40}|T[A-Za-z0-9]{33})\b/', $rawSpeech, $m)) {
                return ['value' => $m[1], 'display_value' => $m[1]];
            }
        }

        // Special case: Email Address
        if ($fieldDef['type'] === 'email' || $fieldName === 'email') {
            if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $rawSpeech, $m)) {
                return ['value' => $m[0], 'display_value' => $m[0]];
            }
        }

        // Special case: Phone / Mobile
        if ($fieldDef['type'] === 'tel' || str_contains(strtolower($fieldName), 'mobile') || str_contains(strtolower($fieldName), 'phone')) {
            if ($this->isFieldMentioned($normSpeech, $fieldDef)) {
                $phone = IraTextNormalizer::normalizePhoneNumber($rawSpeech);
                if ($phone !== null) {
                    return ['value' => $phone, 'display_value' => $phone];
                }
            }
        }

        // General text extraction: Check if field is mentioned
        if ($this->isFieldMentioned($normSpeech, $fieldDef)) {
            $aliases = $fieldDef['aliases'] ?? [];
            $aliases[] = $fieldDef['name'];
            $patterns = array_map(fn($a) => preg_quote($this->normalizeUtterance($a), '/'), $aliases);
            $aliasRegex = implode('|', $patterns);

            // "naam Ramesh likho" / "subject Account verification" / "message mein payment status likho"
            if (preg_match('/(?:' . $aliasRegex . ')\s*(?:mein|me|ko|hai|h|par)?\s*[:=]?\s*(?:likho|daalo|karo|bharo)?\s+([^,.;]+?)(?:\s*(?:likho|daalo|karo|bharo|aur|\.|$))/iu', $rawSpeech, $m)) {
                $val = trim($m[1]);
                // Clean punctuation
                $val = trim($val, " '\"");
                if ($val !== '') {
                    $isName = str_contains(strtolower($fieldName), 'name') || str_contains(strtolower($fieldDef['label'] ?? ''), 'name');
                    $val = IraTextNormalizer::transliterate($val, $isName);
                    return ['value' => $val, 'display_value' => $val];
                }
            }
        }

        return null;
    }

    /**
     * Extract normalized Member ID from utterance.
     * Supports continuous 'GAB10025', spaced 'G A B 1 0 0 2 5', or lowercase 'gab10025'.
     */
    protected function extractMemberId(string $normSpeech, array $fieldDef): ?array
    {
        // 1. Standard alphanumeric: GAB10025 or GAB 10025
        if (preg_match('/\b(gab)\s*(\d{4,8})\b/iu', $normSpeech, $m)) {
            $id = strtoupper('GAB' . $m[2]);
            return ['value' => $id, 'display_value' => $id];
        }

        // 2. Spaced letters: "G A B 1 0 0 2 5"
        if (preg_match('/\b(g)\s*(a)\s*(b)\s*([\d\s]{4,10})\b/iu', $normSpeech, $m)) {
            $digits = preg_replace('/\D/', '', $m[4]);
            if (strlen($digits) >= 4) {
                $id = 'GAB' . $digits;
                return ['value' => $id, 'display_value' => $id];
            }
        }

        // 3. Generic alphanumeric ID when field is explicitly mentioned
        // e.g. "member id ABC12345"
        if ($this->isFieldMentioned($normSpeech, $fieldDef)) {
            if (preg_match('/\b([a-zA-Z]{2,4}\d{4,8})\b/', $normSpeech, $m)) {
                $id = strtoupper($m[1]);
                return ['value' => $id, 'display_value' => $id];
            }
        }

        return null;
    }

    /**
     * Parse spoken Hindi numbers into integers.
     * e.g. "pachas" -> 50, "ek sau" -> 100, "do hazaar" -> 2000, "dedh sau" -> 150.
     */
    public function parseHindiSpokenNumber(string $text): ?int
    {
        $clean = mb_strtolower(trim($text), 'UTF-8');

        // 1. Strip imperative auxiliary verbs (kar do, bhar do, daal do, etc.) and command verbs
        // so that the auxiliary verb "do" does not collide with the number 2 ("do")
        $clean = preg_replace('/\b(kar\s*do|bhar\s*do|daal\s*do|rakh\s*do|bhej\s*do|likh\s*do|bata\s*do|kardo|bhardo|daaldo|rakhdo|karo|daalo|bharo|rakho)\b/iu', ' ', $clean);

        $words = [
            // Units
            'shunya' => 0, 'zero' => 0,
            'ek' => 1, 'एक' => 1,
            'do' => 2, 'दो' => 2,
            'teen' => 3, 'तीन' => 3,
            'chaar' => 4, 'char' => 4, 'चार' => 4,
            'paanch' => 5, 'panch' => 5, 'पांच' => 5,
            'chhah' => 6, 'chhe' => 6, 'छह' => 6,
            'saat' => 7, 'सात' => 7,
            'aath' => 8, 'आठ' => 8,
            'nau' => 9, 'नौ' => 9,
            'das' => 10, 'दस' => 10,

            // Teens & twenties
            'gyarah' => 11, 'ग्यारह' => 11,
            'barah' => 12, 'बारह' => 12,
            'terah' => 13, 'तेरह' => 13,
            'chaudah' => 14, 'चौदह' => 14,
            'pandrah' => 15, 'पंद्रह' => 15,
            'solah' => 16, 'सोलह' => 16,
            'satrah' => 17, 'सत्रह' => 17,
            'atharah' => 18, 'अठारह' => 18,
            'unnis' => 19, 'उन्नीस' => 19,
            'bees' => 20, 'बीस' => 20,

            // Tens
            'tees' => 30, 'तीस' => 30,
            'chaalees' => 40, 'chalis' => 40, 'चालीस' => 40,
            'pachas' => 50, 'pachaas' => 50, 'पचास' => 50,
            'saath' => 60, 'sath' => 60, 'साठ' => 60,
            'sattar' => 70, 'सत्तर' => 70,
            'assi' => 80, 'अस्सी' => 80,
            'nabbe' => 90, 'नब्बे' => 90,

            // Multipliers
            'sau' => 100, 'so' => 100, 'सौ' => 100,
            'hazaar' => 1000, 'hazar' => 1000, 'हज़ार' => 1000, 'हजार' => 1000,
            'lakh' => 100000, 'लाख' => 100000,
            'karod' => 10000000, 'crore' => 10000000, 'करोड़' => 10000000,

            // Fractions / Compound
            'dedh hazaar' => 1500, 'dedh hazar' => 1500, 'डेढ़ हज़ार' => 1500,
            'dhai hazaar' => 2500, 'dhai hazar' => 2500, 'ढाई हज़ार' => 2500,
            'dedh sau' => 150, 'डेढ़ सौ' => 150,
            'dhai sau' => 250, 'ढाई सौ' => 250,
        ];

        // 2. Check for number + multiplier patterns e.g. "do hazaar", "5 sau", "teen lakh"
        if (preg_match('/\b(\d+|ek|do|teen|chaar|char|paanch|panch|chhah|chhe|saat|aath|nau|das|bees|tees|chalis|chaalees|pachas|pachaas|saath|sattar|assi|nabbe)\s*(hazaar|hazar|हज़ार|हजार|sau|so|सौ|lakh|लाख|karod|crore|करोड़)\b/iu', $clean, $m)) {
            $multWord = $m[2];
            $multVal = $words[$multWord] ?? 1000;
            $numPart = $m[1];
            $numVal = is_numeric($numPart) ? (int) $numPart : ($words[$numPart] ?? 1);
            return $numVal * $multVal;
        }

        // 3. Sort dictionary by length descending so multi-word compounds ("dedh sau") match before ("sau")
        uksort($words, fn($a, $b) => mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8'));

        // 4. Word boundary match
        foreach ($words as $phrase => $val) {
            if (preg_match('/(?:\b|^)' . preg_quote($phrase, '/') . '(?:\b|$)/iu', $clean)) {
                return $val;
            }
        }

        return null;
    }

    /**
     * Cross-validate Staking package amount limits.
     */
    protected function validateStakingLimits(string $package, float $amount): ?string
    {
        $limits = [
            'Silver' => ['min' => 100, 'max' => 1000],
            'Gold' => ['min' => 1100, 'max' => 5000],
            'Platinum' => ['min' => 5100, 'max' => 10000],
            'Diamond' => ['min' => 10100, 'max' => null],
        ];

        if (!isset($limits[$package])) {
            return null;
        }

        $min = $limits[$package]['min'];
        $max = $limits[$package]['max'];

        if ($amount < $min) {
            return "Note: {$package} package ke liye minimum amount \${$min} hai. Aapne \${$amount} select kiya hai.";
        }

        if ($max !== null && $amount > $max) {
            return "Note: {$package} package ke liye maximum amount \${$max} hai. Aapne \${$amount} select kiya hai.";
        }

        return null;
    }

    /**
     * Build conversational speech confirmation for applied updates.
     */
    protected function buildSpeechConfirmation(
        array $updates,
        array $sensitiveSkipped,
        array $warnings,
        array $schema,
        array $currentFields
    ): string {
        $phrases = [];

        foreach ($updates as $u) {
            $lbl = $u['label'];
            $val = $u['display_value'] ?? $u['value'];

            if ($u['type'] === 'select' || $u['type'] === 'radio') {
                $phrases[] = "{$lbl} {$val} select kiya";
            } elseif ($u['type'] === 'checkbox') {
                $phrases[] = "{$lbl} tick kiya";
            } else {
                $phrases[] = "{$lbl} mein {$val} daal diya";
            }
        }

        $speech = implode(' aur ', $phrases) . ' hai.';

        if (!empty($sensitiveSkipped)) {
            $speech .= ' Suraksha ke liye, kripya apna password ya PIN screen par manually enter karein.';
        }

        if (!empty($warnings)) {
            $speech .= ' ' . implode(' ', $warnings);
        }

        return $speech;
    }

    /**
     * Calculate which required fields are still missing/empty (strictly excluding sensitive, read-only, hidden, or unavailable fields).
     */
    protected function calculateRemainingRequired(array $schema, array $currentFields, ?array $domEligibleKeys = null): array
    {
        $remaining = [];

        foreach ($schema['fields'] as $key => $field) {
            if (!($field['required'] ?? false)) {
                continue;
            }
            if ($this->isSensitiveField($field) || $this->isReadOnlyOrHiddenField($field) || ($field['file_upload'] ?? false)) {
                continue;
            }
            if (is_array($domEligibleKeys) && !empty($domEligibleKeys)) {
                $fName = $field['name'] ?? '';
                if (!in_array($key, $domEligibleKeys, true) && !in_array($fName, $domEligibleKeys, true)) {
                    continue;
                }
            }

            $currentVal = $currentFields[$key] ?? null;
            if ($currentVal === null || trim((string) $currentVal) === '') {
                $remaining[] = $field['label'];
            }
        }

        return $remaining;
    }

    /**
     * Check if user is asking what fields are left to fill.
     */
    protected function isRemainingFieldsQuery(string $normSpeech): bool
    {
        return (bool) preg_match('/\b(kya\s*bharna\s*baaki\s*hai|aur\s*kya\s*bharna\s*hai|kya\s*reh\s*gaya|baaki\s*kya\s*hai|what\s*is\s*left|remaining\s*fields?)\b/iu', $normSpeech);
    }

    /**
     * Handle "What is left to fill?" query.
     */
    protected function handleRemainingFieldsQuery(array $schema, array $currentFields): array
    {
        $remaining = $this->calculateRemainingRequired($schema, $currentFields);

        if (empty($remaining)) {
            $speech = "Is form ke sabhi zaroori fields bhare hue hain. Aap check karke submit kar sakte hain.";
        } else {
            $fieldsList = implode(', ', $remaining);
            $speech = "Is form mein abhi yeh fields bharna baaki hai: {$fieldsList}.";
        }

        return [
            'success' => true,
            'status' => 'status_query',
            'speech' => $speech,
            'form_key' => $schema['key'],
            'form_selector' => $schema['selector'],
            'form_updates' => [],
            'remaining_required' => $remaining,
        ];
    }

    /**
     * Check if user issued a Reset / Clear Form command.
     */
    protected function isResetFormCommand(string $normSpeech): bool
    {
        return (bool) preg_match('/\b(reset\s*form|clear\s*form|form\s*reset|form\s*clear|reset\s*karo|form\s*clear\s*karo|form\s*reset\s*karo|sab\s*hata\s*do|clear\s*all|start\s*over|sab\s*clear\s*karo)\b/iu', $normSpeech);
    }

    /**
     * Handle Reset / Clear Form command.
     */
    protected function handleResetFormCommand(array $schema): array
    {
        $updates = [];

        foreach ($schema['fields'] as $key => $field) {
            if ($field['read_only'] ?? false) {
                continue;
            }

            $updates[] = [
                'field' => $key,
                'name' => $field['name'],
                'selector' => $field['selector'],
                'value' => '',
                'display_value' => '',
                'label' => $field['label'],
                'type' => $field['type'],
                'events' => $field['dynamic_events'] ?? ['input', 'change'],
            ];
        }

        return [
            'success' => true,
            'status' => 'form_reset',
            'speech' => "Maine form ke sabhi fields clear kar diye hain. Aap dubara shuru kar sakte hain.",
            'form_key' => $schema['key'],
            'form_selector' => $schema['selector'],
            'form_updates' => array_values($updates),
            'remaining_required' => $this->calculateRemainingRequired($schema, []),
        ];
    }

    /**
     * LLM Fallback extraction via Gemini (gemini-3.5-flash-lite).
     */
    protected function extractWithGemini(string $speech, array $schema, array $currentFields): array
    {
        try {
            $aiResult = $this->formBridge->extractFieldValues($speech, $schema, $currentFields);

            if ($aiResult['success'] && !empty($aiResult['updates'])) {
                $validUpdates = [];
                foreach ($aiResult['updates'] as $fKey => $val) {
                    if (isset($schema['fields'][$fKey])) {
                        $fDef = $schema['fields'][$fKey];
                        if (!($fDef['sensitive'] ?? false) && !($fDef['read_only'] ?? false)) {
                            $validUpdates[$fKey] = [
                                'field' => $fKey,
                                'name' => $fDef['name'],
                                'selector' => $fDef['selector'],
                                'value' => $val,
                                'display_value' => (string) $val,
                                'label' => $fDef['label'],
                                'type' => $fDef['type'],
                                'events' => $fDef['dynamic_events'] ?? ['input', 'change'],
                            ];
                        }
                    }
                }
                return [
                    'updates' => $validUpdates,
                    'sensitive_skipped' => [],
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('[IraFormUnderstandingService] Gemini extraction failed: ' . $e->getMessage());
        }

        return ['updates' => [], 'sensitive_skipped' => []];
    }

    /**
     * Resolve explicit direct field targeting, field modifications, and field switches.
     */
    public function resolveDirectFieldTargeting(
        string $clean,
        string $norm,
        array $schema,
        array $currentFields,
        array $guideState,
        string $language,
        ?array $domEligibleKeys = null
    ): ?array {
        $fields = $schema['fields'] ?? [];
        if (empty($fields)) {
            return null;
        }

        // Active field payload for fallback / responses
        $currentFieldKey = $guideState['current_field'] ?? ($guideState['field_sequence'][0] ?? null);
        $activeFieldDef = $currentFieldKey ? ($fields[$currentFieldKey] ?? null) : null;
        $activeFieldPayload = $activeFieldDef ? [
            'key' => $currentFieldKey,
            'name' => $activeFieldDef['name'],
            'selector' => $activeFieldDef['selector'],
            'label' => $activeFieldDef['label'],
            'type' => $activeFieldDef['type'],
        ] : null;

        $eligibleLabels = [];
        foreach ($fields as $fk => $fd) {
            if (!$this->isSensitiveField($fd) && !$this->isReadOnlyOrHiddenField($fd) && !($fd['file_upload'] ?? false)) {
                $eligibleLabels[] = $fd['label'];
            }
        }
        $labelsSummary = $this->formatFieldLabelsList($eligibleLabels);

        // 0. Check for Compound / Multi-field instruction (e.g. "Naam Ramesh Kumar aur country India kar do")
        if (preg_match('/\b(aur|and|,)\b/iu', $clean)) {
            $multiExtraction = $this->extractFieldsDeterministic($clean, $schema, $currentFields);
            if (!empty($multiExtraction['updates']) && count($multiExtraction['updates']) > 1) {
                $multiUpdates = $multiExtraction['updates'];
                $updatedKeys = array_keys($multiUpdates);
                foreach ($multiUpdates as $uk => $ud) {
                    $currentFields[$uk] = $ud['value'];
                }
                $guideState['filled_fields'] = array_unique(array_merge($guideState['filled_fields'] ?? [], $updatedKeys));
                $guideState['handled_fields'] = array_unique(array_merge($guideState['handled_fields'] ?? [], $updatedKeys));

                $isEditForm = $guideState['is_edit_form'] ?? IraFormRegistry::isEditForm($schema);
                $fieldSequence = $guideState['field_sequence'] ?? array_keys($schema['fields']);
                $nextFieldKey = $this->findNextEligibleField(
                    $schema,
                    $fieldSequence,
                    $currentFields,
                    $updatedKeys,
                    $domEligibleKeys,
                    $isEditForm,
                    $guideState['handled_fields']
                );

                if ($nextFieldKey !== null) {
                    $nextFieldDef = $schema['fields'][$nextFieldKey];
                    $nextQ = $this->getFieldPromptQuestion($nextFieldDef, $schema, $language);
                    $labelsWithVals = array_map(fn($u) => "{$u['label']}: {$u['display_value']}", $multiUpdates);

                    if ($language === 'en') {
                        $speech = "Recorded " . implode(', ', $labelsWithVals) . ". Now please specify the {$nextQ}";
                    } elseif ($language === 'hinglish') {
                        $speech = "Maine " . implode(', ', $labelsWithVals) . " bhar diya hai. Ab {$nextQ}";
                    } else {
                        $speech = "मैंने " . implode(', ', $labelsWithVals) . " भर दिया है। अब {$nextQ}";
                    }

                    $guideState['current_field'] = $nextFieldKey;
                    $guideState['step_index'] = array_search($nextFieldKey, $fieldSequence, true) ?: 0;
                    $guideState['status'] = 'in_progress';

                    return [
                        'success' => true,
                        'status' => 'field_updated',
                        'action' => 'prompt',
                        'speech' => $speech,
                        'form_key' => $schema['key'],
                        'form_title' => $schema['title'],
                        'form_selector' => $schema['selector'],
                        'guide_state' => $guideState,
                        'active_field' => [
                            'key' => $nextFieldKey,
                            'name' => $nextFieldDef['name'],
                            'selector' => $nextFieldDef['selector'],
                            'label' => $nextFieldDef['label'],
                            'type' => $nextFieldDef['type'],
                        ],
                        'form_updates' => array_values($multiUpdates),
                    ];
                }

                return $this->buildCompletionPayload($schema, $currentFields, $guideState, $multiUpdates, []);
            }
        }

        // 1. Check if user is referencing a field from another form / foreign field not in this schema
        $foreignConcepts = [
            'stak_package' => ['package', 'plan', 'staking package', 'पैकेज', 'प्लान'],
            'transfer_amount' => ['transfer amount', 'dollars', 'rashi', 'राशि', 'अमाउंट'],
            'p2p_receiver' => ['receiver', 'recipient', 'receiver id', 'kisko bhejna hai'],
            'support_ticket' => ['ticket', 'complaint', 'शिकायत'],
            'wallet_crypto' => ['crypto address', 'वॉलेट एड्रेस'],
            'kyc_docs' => ['pan card', 'pan', 'aadhar', 'passport', 'पैन कार्ड'],
            'bank_details' => ['bank name', 'account number', 'ifsc', 'branch', 'खाता संख्या', 'बैंक'],
        ];

        foreach ($foreignConcepts as $conceptKey => $conceptAliases) {
            $schemaHasConcept = false;
            foreach ($fields as $fk => $fd) {
                $fName = strtolower($fd['name']);
                $fLabel = strtolower($fd['label']);
                foreach ($conceptAliases as $ca) {
                    if (str_contains($fName, $ca) || str_contains($fLabel, $ca) || $fk === $ca) {
                        $schemaHasConcept = true;
                        break 2;
                    }
                }
            }

            if (!$schemaHasConcept) {
                foreach ($conceptAliases as $ca) {
                    $escapedCa = preg_quote($ca, '/');
                    if (
                        preg_match('/(?:^|\s)' . $escapedCa . '\s+(?:.+?)\s*(?:kar\s*do|kardo|likho|daalo|bharo|rakho|set\s*karo|kijiye|hoga)/iu', $clean) ||
                        preg_match('/(?:^|\s)' . $escapedCa . '\s*(?:mein|me|ko|change\s*karke|badal\s*kar|to|as|select|chuno|kar\s*do|kardo|likho|daalo|bharo|[:=])\b/iu', $clean) ||
                        preg_match('/(?:^|\s)' . $escapedCa . '\s*(?:change\s*karna\s*hai|badalna\s*hai|badlo|change\s*karo|dobara\s*badlo|par\s*jao|select\s*karo|chuno)\b/iu', $clean) ||
                        preg_match('/(?:^|\s)(?:change|update|set|badlo)\s+' . $escapedCa . '\b/iu', $clean)
                    ) {
                        return [
                            'success' => false,
                            'status' => 'invalid_field',
                            'action' => 'prompt',
                            'speech' => $language === 'en'
                                ? "This field is not available in the {$schema['title']} form. Please choose from available fields: {$labelsSummary}."
                                : ($language === 'hinglish'
                                    ? "Yeh field {$schema['title']} form mein uplabdh nahi hai. Kripya is form ke fields batayein: {$labelsSummary}."
                                    : "यह field {$schema['title']} form में उपलब्ध नहीं है। कृपया इस form के fields (जैसे: {$labelsSummary}) बताएं।"),
                            'form_key' => $schema['key'],
                            'form_selector' => $schema['selector'],
                            'guide_state' => $guideState,
                            'active_field' => $activeFieldPayload,
                            'form_updates' => [],
                        ];
                    }
                }
            }
        }

        // 2. Build alias map for fields in current schema and detect targeted field
        $matchedTarget = null;
        $matchedAlias = '';

        foreach ($fields as $fieldKey => $fieldDef) {
            $aliases = $this->collectFieldAliases($fieldKey, $fieldDef);
            // Sort aliases longest first
            usort($aliases, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

            foreach ($aliases as $alias) {
                if (mb_strlen($alias) < 2) continue;
                $esc = preg_quote($alias, '/');

                $isTargeted = false;

                // Pattern A: Starts with alias or with change/set + alias
                if (preg_match('/^(?:kripya|please|change|update|set|badlo|dobara\s*badlo)?\s*' . $esc . '\b/iu', $clean)) {
                    $isTargeted = true;
                }
                // Pattern B: Alias followed by connector particle or action tail
                elseif (preg_match('/(?:^|\s)' . $esc . '\s*(?:mein|me|ko|change\s*karke|badal\s*kar|ko\s*badal\s*kar|set\s*karke|rakho|likho|daalo|bharo|ka|ki|ke|to|as|[:=])\b/iu', $clean)) {
                    $isTargeted = true;
                }
                // Pattern C: Alias followed by change command
                elseif (preg_match('/(?:^|\s)' . $esc . '\s*(?:change\s*karna\s*hai|badalna\s*hai|badlo|change\s*karo|dobara\s*badlo|par\s*jao|select\s*karo|chuno|दोबारा\s*बदलो|बदलो|बदल\s*दो|बदलना\s*है)\b/iu', $clean)) {
                    $isTargeted = true;
                }
                // Pattern D: Contrastive "pehle ... tha ab ..."
                elseif (preg_match('/(?:^|\s)' . $esc . '\s*pehle\s*.+?\s*tha\s*(?:ab|lekin|aur)\s*.+?/iu', $clean)) {
                    $isTargeted = true;
                }

                if ($isTargeted) {
                    if ($matchedTarget === null || mb_strlen($alias) > mb_strlen($matchedAlias)) {
                        $matchedTarget = [
                            'key' => $fieldKey,
                            'def' => $fieldDef,
                        ];
                        $matchedAlias = $alias;
                    }
                }
            }
        }

        if ($matchedTarget === null) {
            return null; // Not a direct field targeting command
        }

        $targetKey = $matchedTarget['key'];
        $targetDef = $matchedTarget['def'];

        // 3. Sensitivity check
        if ($this->isSensitiveField($targetDef)) {
            return [
                'success' => false,
                'status' => 'sensitive_field_rejected',
                'action' => 'prompt',
                'speech' => $language === 'en'
                    ? "For security, {$targetDef['label']} cannot be entered via voice. Please enter it manually on screen."
                    : ($language === 'hinglish'
                        ? "Suraksha ke liye, {$targetDef['label']} voice se enter nahi kiya ja sakta. Kripya screen par manually enter karein."
                        : "सुरक्षा कारणों से, {$targetDef['label']} आवाज़ से नहीं भरा जा सकता। कृपया इसे स्क्रीन पर स्वयं भरें।"),
                'form_key' => $schema['key'],
                'form_selector' => $schema['selector'],
                'guide_state' => $guideState,
                'active_field' => $activeFieldPayload,
                'form_updates' => [],
            ];
        }

        // 4. Read-only check
        if ($this->isReadOnlyOrHiddenField($targetDef)) {
            return [
                'success' => false,
                'status' => 'readonly_field_rejected',
                'action' => 'prompt',
                'speech' => $language === 'en'
                    ? "{$targetDef['label']} is a read-only field and cannot be changed."
                    : ($language === 'hinglish'
                        ? "{$targetDef['label']} read-only field hai aur isse badla nahi ja sakta."
                        : "{$targetDef['label']} read-only field है और इसे बदला नहीं जा सकता।"),
                'form_key' => $schema['key'],
                'form_selector' => $schema['selector'],
                'guide_state' => $guideState,
                'active_field' => $activeFieldPayload,
                'form_updates' => [],
            ];
        }

        // 5. File upload check
        if ($targetDef['file_upload'] ?? false) {
            return [
                'success' => false,
                'status' => 'file_upload_rejected',
                'action' => 'prompt',
                'speech' => $language === 'en'
                    ? "{$targetDef['label']} is a file upload field. Please choose the file on screen using the button."
                    : ($language === 'hinglish'
                        ? "{$targetDef['label']} file upload field hai. Kripya screen par Choose File button se file select karein."
                        : "{$targetDef['label']} फ़ाइल अपलोड फ़ील्ड है। कृपया स्क्रीन पर Choose File बटन से फ़ाइल चुनें।"),
                'form_key' => $schema['key'],
                'form_selector' => $schema['selector'],
                'guide_state' => $guideState,
                'active_field' => $activeFieldPayload,
                'form_updates' => [],
            ];
        }

        // 6. Eligible field: Check if switch-only or value provided
        $escMatched = preg_quote($matchedAlias, '/');
        $rawRest = preg_replace('/^(?:change|update|set|badlo|kripya|please)?\s*' . $escMatched . '\s*(?:mein|me|ko|change\s*karke|badal\s*kar|ko\s*badal\s*kar|set\s*karke|to|as|hai|rakho|likho|daalo|bharo|[:=])?\s*/iu', '', $clean);
        $rawRest = trim($rawRest);

        $isSwitchOnly = ($rawRest === '') || preg_match('/^(?:change(?:\s*karna\s*hai|\s*karna|\s*karo)?|badalna(?:\s*hai)?|badlo|dobara\s*badlo|par\s*jao|select(?:\s*karo)?|chuno|दोबारा\s*बदलो|बदलो|बदल\s*दो|बदलना\s*है|change|dobara|karna\s*hai|karna)$/iu', $rawRest);

        $rest = preg_replace('/\s*(?:likho|daalo|karo|bharo|kardo|kar\s*do|kar\s*de|kar\s*dijiye|kijiye|rakho|rakh\s*do|set\s*karo|set\s*kardo|set\s*kar\s*do|hoga|hai)$/iu', '', $rawRest);
        $rest = trim($rest);

        if ($isSwitchOnly) {
            $guideState['current_field'] = $targetKey;
            $guideState['step_index'] = array_search($targetKey, $guideState['field_sequence'] ?? []) ?: 0;
            $q = $this->getFieldPromptQuestion($targetDef, $schema, $language);

            $switchSpeech = $language === 'en'
                ? "Sure, let's update {$targetDef['label']}. {$q}"
                : ($language === 'hinglish'
                    ? "Theek hai, {$targetDef['label']} ko dobara badalte hain. {$q}"
                    : "ठीक है, {$targetDef['label']} को दोबारा बदलते हैं। {$q}");

            return [
                'success' => true,
                'status' => 'switch_field',
                'action' => 'prompt',
                'speech' => $switchSpeech,
                'form_key' => $schema['key'],
                'form_selector' => $schema['selector'],
                'guide_state' => $guideState,
                'active_field' => [
                    'key' => $targetKey,
                    'name' => $targetDef['name'],
                    'selector' => $targetDef['selector'],
                    'label' => $targetDef['label'],
                    'type' => $targetDef['type'],
                ],
                'form_updates' => [],
            ];
        }

        // 7. Value is provided: Extract and apply update
        $evalSpeech = $rest !== '' ? $rest : $clean;
        $extracted = $this->extractTargetedFieldValue($evalSpeech, $this->normalizeUtterance($evalSpeech), $targetDef, $schema);

        if ($extracted === null) {
            $extracted = $this->extractTargetedFieldValue($clean, $norm, $targetDef, $schema);
        }

        if ($extracted === null && $this->formBridge->isConfigured()) {
            $aiRes = $this->formBridge->extractFieldValues($clean, $schema, $currentFields, $targetKey);
            if ($aiRes['success'] && !empty($aiRes['updates']) && isset($aiRes['updates'][$targetKey])) {
                $extracted = [
                    'value' => $aiRes['updates'][$targetKey],
                    'display_value' => (string) $aiRes['updates'][$targetKey],
                    'is_correction' => $aiRes['is_correction'] ?? true,
                ];
            }
        }

        if ($extracted === null) {
            $guideState['current_field'] = $targetKey;
            $guideState['step_index'] = array_search($targetKey, $guideState['field_sequence'] ?? []) ?: 0;
            $clarifySpeech = $this->buildFieldClarificationSpeech($targetDef, $language);

            return [
                'success' => false,
                'status' => 'clarify_field',
                'action' => 'prompt',
                'speech' => $clarifySpeech,
                'form_key' => $schema['key'],
                'form_selector' => $schema['selector'],
                'guide_state' => $guideState,
                'active_field' => [
                    'key' => $targetKey,
                    'name' => $targetDef['name'],
                    'selector' => $targetDef['selector'],
                    'label' => $targetDef['label'],
                    'type' => $targetDef['type'],
                ],
                'form_updates' => [],
            ];
        }

        $val = $extracted['value'];
        $valDisp = $extracted['display_value'] ?? (string) $val;
        $spokenVal = $this->formatSpokenValue($valDisp, $targetDef, $language);

        $currentFields[$targetKey] = $val;
        $guideState['filled_fields'] = array_unique(array_merge($guideState['filled_fields'] ?? [], [$targetKey]));
        $guideState['handled_fields'] = array_unique(array_merge($guideState['handled_fields'] ?? [], [$targetKey]));

        $updates = [
            $targetKey => [
                'field' => $targetKey,
                'name' => $targetDef['name'],
                'selector' => $targetDef['selector'],
                'value' => $val,
                'display_value' => $valDisp,
                'spoken_value' => $spokenVal,
                'label' => $targetDef['label'],
                'type' => $targetDef['type'],
                'events' => $targetDef['dynamic_events'] ?? ['input', 'change'],
            ]
        ];

        $isEditForm = $guideState['is_edit_form'] ?? IraFormRegistry::isEditForm($schema);
        $fieldSequence = $guideState['field_sequence'] ?? array_keys($schema['fields']);
        $nextFieldKey = $this->findNextEligibleField(
            $schema,
            $fieldSequence,
            $currentFields,
            [$targetKey],
            $domEligibleKeys,
            $isEditForm,
            $guideState['handled_fields']
        );

        if ($nextFieldKey !== null) {
            $nextFieldDef = $schema['fields'][$nextFieldKey];
            $nextQ = $this->getFieldPromptQuestion($nextFieldDef, $schema, $language);

            $speech = $language === 'en'
                ? "{$targetDef['label']} has been changed to {$spokenVal}. Now please specify the {$nextQ}"
                : ($language === 'hinglish'
                    ? "Theek hai, {$targetDef['label']} ko badal kar {$spokenVal} kar diya hai. Ab {$nextQ}"
                    : "ठीक है, {$targetDef['label']} को बदल कर {$spokenVal} कर दिया है। अब {$nextQ}");

            $guideState['current_field'] = $nextFieldKey;
            $guideState['step_index'] = array_search($nextFieldKey, $fieldSequence, true) ?: 0;
            $guideState['status'] = 'in_progress';

            return [
                'success' => true,
                'status' => 'field_updated',
                'action' => 'prompt',
                'speech' => $speech,
                'form_key' => $schema['key'],
                'form_title' => $schema['title'],
                'form_selector' => $schema['selector'],
                'guide_state' => $guideState,
                'active_field' => [
                    'key' => $nextFieldKey,
                    'name' => $nextFieldDef['name'],
                    'selector' => $nextFieldDef['selector'],
                    'label' => $nextFieldDef['label'],
                    'type' => $nextFieldDef['type'],
                ],
                'form_updates' => array_values($updates),
            ];
        }

        return $this->buildCompletionPayload($schema, $currentFields, $guideState, $updates, [], $targetDef, $valDisp, true);
    }

    /**
     * Collect comprehensive aliases for a field across English, Hinglish, and Devanagari.
     */
    protected function collectFieldAliases(string $fieldKey, array $fieldDef): array
    {
        $aliases = $fieldDef['aliases'] ?? [];
        $aliases[] = $fieldKey;
        $aliases[] = $fieldDef['name'] ?? '';

        $label = $fieldDef['label'] ?? '';
        $aliases[] = $label;
        $cleanLabel = trim(preg_replace('/\s*\(.*?\)/', '', $label));
        if ($cleanLabel !== '' && $cleanLabel !== $label) {
            $aliases[] = $cleanLabel;
        }

        $lowerKey = strtolower($fieldKey);
        $lowerName = strtolower($fieldDef['name'] ?? '');
        $lowerLabel = strtolower($label);

        if (str_contains($lowerKey, 'name') || str_contains($lowerName, 'name') || str_contains($lowerLabel, 'name')) {
            $aliases = array_merge($aliases, ['name', 'naam', 'nam', 'full name', 'poora naam', 'पूरा नाम', 'नाम', 'मेरा नाम']);
        }
        if (str_contains($lowerKey, 'mobile') || str_contains($lowerName, 'mobile') || str_contains($lowerLabel, 'mobile') || str_contains($lowerLabel, 'phone')) {
            $aliases = array_merge($aliases, ['mobile', 'phone', 'contact', 'mobile number', 'phone number', 'फ़ोन', 'मोबाइल', 'मोबाइल नंबर', 'संपर्क']);
        }
        if (str_contains($lowerKey, 'country') || str_contains($lowerName, 'country') || str_contains($lowerLabel, 'country')) {
            $aliases = array_merge($aliases, ['country', 'desh', 'nation', 'videsh', 'कंट्री', 'देश', 'राष्ट्र']);
        }
        if (str_contains($lowerKey, 'email') || str_contains($lowerName, 'email') || str_contains($lowerLabel, 'email')) {
            $aliases = array_merge($aliases, ['email', 'e-mail', 'mail', 'ईमेल', 'मेल']);
        }
        if (str_contains($lowerKey, 'amount') || str_contains($lowerName, 'amount') || str_contains($lowerLabel, 'amount')) {
            $aliases = array_merge($aliases, ['amount', 'rashi', 'paise', 'dollars', 'rakam', 'रकम', 'राशि', 'अमाउंट', 'पैसे', 'डॉलर']);
        }
        if (str_contains($lowerKey, 'package') || str_contains($lowerName, 'package') || str_contains($lowerLabel, 'package')) {
            $aliases = array_merge($aliases, ['package', 'plan', 'staking package', 'पैकेज', 'प्लान']);
        }
        if (str_contains($lowerKey, 'memberid') || str_contains($lowerName, 'memberid') || str_contains($lowerLabel, 'member id') || str_contains($lowerLabel, 'receiver')) {
            $aliases = array_merge($aliases, ['member id', 'memberid', 'receiver id', 'receiver member id', 'recipient', 'user id', 'मेंबर आईडी', 'सदस्य आईडी']);
        }
        if (str_contains($lowerKey, 'subject') || str_contains($lowerName, 'subject') || str_contains($lowerLabel, 'subject')) {
            $aliases = array_merge($aliases, ['subject', 'topic', 'mudda', 'vishay', 'विषय', 'सब्जेक्ट']);
        }
        if (str_contains($lowerKey, 'issue') || str_contains($lowerName, 'issue') || str_contains($lowerLabel, 'issue') || str_contains($lowerLabel, 'message') || str_contains($lowerLabel, 'reply')) {
            $aliases = array_merge($aliases, ['issue', 'message', 'reply', 'sandesh', 'विवरण', 'मैसेज', 'संदेश']);
        }
        if (str_contains($lowerKey, 'pass') || str_contains($lowerName, 'pass') || str_contains($lowerLabel, 'password') || str_contains($lowerLabel, 'pin')) {
            $aliases = array_merge($aliases, ['password', 'txn password', 'pin', 'tpin', 'security pin', 'पासवर्ड', 'पिन']);
        }
        if (str_contains($lowerKey, 'wallet') || str_contains($lowerName, 'wallet') || str_contains($lowerLabel, 'wallet')) {
            $aliases = array_merge($aliases, ['wallet address', 'address', 'crypto address', 'वॉलेट एड्रेस', 'पता']);
        }
        if (str_contains($lowerKey, 'image') || str_contains($lowerName, 'image') || str_contains($lowerLabel, 'image') || str_contains($lowerLabel, 'photo')) {
            $aliases = array_merge($aliases, ['profile image', 'photo', 'picture', 'image', 'फोटो', 'तस्वीर']);
        }

        $cleaned = [];
        foreach ($aliases as $a) {
            $t = trim((string) $a);
            if ($t !== '' && !in_array($t, $cleaned, true)) {
                $cleaned[] = $t;
            }
        }
        return $cleaned;
    }

    /**
     * Normalize utterance for robust matching across Devanagari, Hindi, and English.
     */
    protected function normalizeUtterance(string $text): string
    {
        $clean = mb_strtolower(trim($text), 'UTF-8');

        // Normalize Devanagari digits to ASCII digits
        $devanagariDigits = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
        $asciiDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $clean = str_replace($devanagariDigits, $asciiDigits, $clean);

        // Normalize punctuation
        $clean = preg_replace('/[?!,;:()"\']+/u', ' ', $clean);
        $clean = preg_replace('/\s+/u', ' ', $clean);

        return trim($clean);
    }
}
