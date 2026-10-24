<?php

namespace App\Services\AI;

/**
 * IraTextNormalizer
 *
 * Normalizes speech input across English, Roman Hinglish, and Devanagari Hindi
 * to strictly produce Roman/English characters and standard numeric digits.
 *
 * Rules:
 * 1. Names: "अभि कुमार" -> "Abhi Kumar"
 * 2. Countries: "कनाडा" -> "Canada", "भारत" -> "India"
 * 3. Free text: "मुझे account access नहीं हो रहा" -> "Mujhe account access nahi ho raha"
 * 4. Numeric amounts: "एक हजार पांच सौ" -> 1500, "बारह हजार" -> 12000
 * 5. Phone numbers: "९८७६५४३२१०" -> "9876543210", "1234567890" -> "1234567890"
 * 6. Devanagari digits: ०-९ -> 0-9
 * 7. Preservation of leading zeros on phone numbers and IDs.
 */
class IraTextNormalizer
{
    /**
     * Common geographical, entity and vocabulary dictionary.
     */
    protected static array $knownWords = [
        // Countries
        'कनाडा' => 'Canada',
        'भारत' => 'India',
        'इंडिया' => 'India',
        'अमेरिका' => 'USA',
        'यूएसए' => 'USA',
        'ब्रिटेन' => 'UK',
        'इंग्लैंड' => 'England',
        'दुबई' => 'Dubai',
        'ऑस्ट्रेलिया' => 'Australia',
        'जर्मनी' => 'Germany',
        'फ्रांस' => 'France',
        'सिंगापुर' => 'Singapore',
        'मलेशिया' => 'Malaysia',
        'नेपाल' => 'Nepal',
        'भूटान' => 'Bhutan',
        'रूस' => 'Russia',
        'रशिया' => 'Russia',
        'जापान' => 'Japan',
        'चीन' => 'China',
        'यूएई' => 'UAE',

        // Common free text words in support / messages
        'मुझे' => 'Mujhe',
        'नहीं' => 'nahi',
        'हो' => 'ho',
        'रहा' => 'raha',
        'रही' => 'rahi',
        'रहे' => 'rahe',
        'है' => 'hai',
        'हैं' => 'hain',
        'था' => 'tha',
        'थी' => 'thi',
        'थे' => 'the',
        'करना' => 'karna',
        'कर' => 'kar',
        'दो' => 'do',
        'दीजिए' => 'dijiye',
        'चाहिए' => 'chahiye',
        'कृपया' => 'kripya',
        'मदद' => 'madad',
        'समस्या' => 'samasiya',
        'खाता' => 'khata',
        'पासवर्ड' => 'password',
        'पैसा' => 'paise',
        'पैसे' => 'paise',
        'विथड्रॉल' => 'withdrawal',
        'डिपॉजिट' => 'deposit',
        'ट्रांसफर' => 'transfer',
        'अकाउंट' => 'account',
        'एक्सेस' => 'access',
        'लॉगिन' => 'login',
        'साइनअप' => 'signup',
        'ओटीपी' => 'OTP',
        'पिन' => 'PIN',
        'नमस्ते' => 'Namaste',
        'धन्यवाद' => 'Dhanyavaad',
        'शुक्रिया' => 'Shukriya',
    ];

    /**
     * Devanagari vowels (independent).
     */
    protected static array $vowels = [
        'अ' => 'a', 'आ' => 'aa', 'इ' => 'i', 'ई' => 'i',
        'उ' => 'u', 'ऊ' => 'u', 'ऋ' => 'ri',
        'ए' => 'e', 'ऐ' => 'ai', 'ओ' => 'o', 'औ' => 'au',
    ];

    /**
     * Devanagari vowel signs (matras).
     */
    protected static array $matras = [
        'ा' => 'a', 'ि' => 'i', 'ी' => 'i',
        'ु' => 'u', 'ू' => 'u', 'ृ' => 'ri',
        'े' => 'e', 'ै' => 'ai', 'ो' => 'o', 'ौ' => 'au',
    ];

    /**
     * Devanagari consonants (without inherent vowel).
     */
    protected static array $consonants = [
        'क' => 'k', 'ख' => 'kh', 'ग' => 'g', 'घ' => 'gh', 'ङ' => 'ng',
        'च' => 'ch', 'छ' => 'chh', 'ज' => 'j', 'झ' => 'jh', 'ञ' => 'ny',
        'ट' => 't', 'ठ' => 'th', 'ड' => 'd', 'ढ' => 'dh', 'ण' => 'n',
        'त' => 't', 'थ' => 'th', 'द' => 'd', 'ध' => 'dh', 'न' => 'n',
        'प' => 'p', 'फ' => 'ph', 'ब' => 'b', 'भ' => 'bh', 'म' => 'm',
        'य' => 'y', 'र' => 'r', 'ल' => 'l', 'व' => 'v',
        'श' => 'sh', 'ष' => 'sh', 'स' => 's', 'ह' => 'h',

        // Nukta consonants
        'क़' => 'q', 'ख़' => 'kh', 'ग़' => 'gh', 'ज़' => 'z',
        'ड़' => 'r', 'ढ़' => 'rh', 'फ़' => 'f',
    ];

    /**
     * Spoken Hindi / English digit tokens for phone number parsing.
     */
    protected static array $digitWords = [
        'shunya' => '0', 'zero' => '0', 'शून्य' => '0', 'ज़ीरो' => '0',
        'ek' => '1', 'one' => '1', 'एक' => '1',
        'do' => '2', 'two' => '2', 'दो' => '2',
        'teen' => '3', 'three' => '3', 'तीन' => '3',
        'chaar' => '4', 'four' => '4', 'चार' => '4',
        'paanch' => '5', 'five' => '5', 'पांच' => '5', 'पाँच' => '5',
        'chhah' => '6', 'che' => '6', 'chhe' => '6', 'six' => '6', 'छह' => '6', 'छः' => '6',
        'saat' => '7', 'seven' => '7', 'सात' => '7',
        'aath' => '8', 'eight' => '8', 'आठ' => '8',
        'nau' => '9', 'nine' => '9', 'नौ' => '9',
    ];

    /**
     * Check if a text contains Devanagari script.
     */
    public static function hasDevanagari(string $text): bool
    {
        return (bool) preg_match('/[\x{0900}-\x{097F}]/u', $text);
    }

    /**
     * Convert Devanagari numerals (०-९) to ASCII digits (0-9).
     */
    public static function convertDevanagariDigits(string $text): string
    {
        $devanagari = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
        $ascii = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($devanagari, $ascii, $text);
    }

    /**
     * Check if a field definition represents an alphanumeric or numeric identifier.
     */
    public static function isIdentifierField(array $fieldDef): bool
    {
        $type = strtolower($fieldDef['type'] ?? '');
        $name = strtolower($fieldDef['name'] ?? '');
        $label = strtolower($fieldDef['label'] ?? '');

        if ($type === 'identifier') {
            return true;
        }

        return str_contains($name, 'memberid') ||
               str_contains($name, 'member_id') ||
               str_contains($name, 'user_id') ||
               str_contains($name, 'username') ||
               str_contains($name, 'sponsor') ||
               str_contains($name, 'searchid') ||
               str_contains($name, 'receiverid') ||
               str_contains($name, 'recipientid') ||
               str_contains($label, 'member id') ||
               str_contains($label, 'sponsor id') ||
               str_contains($label, 'user id') ||
               str_contains($label, 'recipient id') ||
               str_contains($label, 'receiver id') ||
               str_contains($label, 'receiver member id');
    }

    /**
     * Normalize spoken alphanumeric identifiers (Member ID, Sponsor ID, etc.)
     *
     * Rules:
     * - Spoken letters: "Q R V", "क्यू आर वी", "ay bee cee", "cue are vee" -> Latin uppercase "QRV"
     * - Compound Devanagari letter names: "क्यूआरवी 123456", "जीएबी 10025" -> "QRV123456", "GAB10025"
     * - Spoken digits: "one two three", "one zero zero two five", "वन टू थ्री", "शून्य पाँच तीन" -> "123", "10025", "053"
     * - Preserves leading zeros (e.g. "zero five three" -> "053").
     * - Preserves existing Latin alphabet characters and valid identifier symbols (GAB10025 -> GAB10025).
     * - Multi-word letters: "double u" / "डबल यू" -> "W".
     * - Ignores conversational fillers (e.g. "Receiver Member ID क्यू आर वी 123456", "likho", "daalo").
     * - Never parses the digit sequence as an integer/float quantity.
     *
     * @param string $text
     * @return string|null Normalized uppercase alphanumeric string or null if unresolvable
     */
    public static function normalizeIdentifier(string $text): ?string
    {
        $clean = trim($text);
        if ($clean === '') {
            return null;
        }

        // 0. Remove zero-width characters (ZWNJ, ZWJ, etc.) and normalize whitespace
        $clean = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $clean);
        $clean = preg_replace('/[\x{00A0}\s]+/u', ' ', $clean);

        // Convert Devanagari numerals ०-९ to 0-9
        $clean = self::convertDevanagariDigits($clean);

        // 1. Strip conversational command fillers and field titles
        $fillers = [
            '/\b(receiver\s*member\s*id|recipient\s*member\s*id|target\s*member\s*id|search\s*member\s*id)\b/iu',
            '/(?:^|\s)(रिसीवर\s*मेंबर\s*आईडी|प्राप्तकर्ता\s*मेंबर\s*आईडी)(?:$|\s)/u',
            '/\b(member\s*id|sponsor\s*id|user\s*id|receiver\s*id|recipient\s*id|target\s*id|search\s*id|username|id)\b/iu',
            '/(?:^|\s)(मेंबर\s*आईडी|सदस्य\s*आईडी|स्पॉन्सर\s*आईडी|यूज़र\s*आईडी|रिसीवर\s*आईडी|आईडी)(?:$|\s)/u',
            '/\b(likho|daalo|bharo|rakho|karo|hoga|kardo|kijiye|hai|yeh\s*hai|mera|meri|kripya|please|enter|likh\s*do|bhar\s*do|set\s*karo)\b/iu',
            '/(?:^|\s)(लिखो|डालो|भरो|रखो|करो|होगा|कर\s*दो|कीजिए|है|यह\s*है|मेरा|मेरी|कृपया|भर\s*दो|लिख\s*दो)(?:$|\s)/u',
        ];
        foreach ($fillers as $fRegex) {
            $clean = preg_replace($fRegex, ' ', $clean);
        }

        // 2. Multi-word letter names (e.g. "double u" -> "W", "डबल यू" -> "W")
        $clean = preg_replace('/\bdouble\s*(?:u|you)\b/iu', ' W ', $clean);
        $clean = preg_replace('/(?:^|\s)डबल\s*(?:यू|यु)(?:$|\s)/u', ' W ', $clean);

        // Separate attached digits from letters e.g. "क्यूआरवी123456" -> "क्यूआरवी 123456"
        $clean = preg_replace('/([a-zA-Z\x{0900}-\x{097F}])([0-9])/u', '$1 $2', $clean);
        $clean = preg_replace('/([0-9])([a-zA-Z\x{0900}-\x{097F}])/u', '$1 $2', $clean);

        $clean = trim(preg_replace('/\s+/u', ' ', $clean));
        if ($clean === '') {
            return null;
        }

        // If string is already a clean alphanumeric identifier without spaces (e.g. "GAB10025" or "QRV123456")
        if (preg_match('/^[A-Za-z0-9_-]{2,30}$/', $clean)) {
            return strtoupper($clean);
        }

        // A multi-token identifier must contain at least one digit, digit word, or explicit identifier clue.
        // Pure prose utterances with spaces (e.g. "kuch bhi ulta seedha bolo") are never valid identifiers.
        $hasDigitOrIdClue = preg_match('/\d/u', $clean) ||
            preg_match('/\b(zero|one|two|three|four|five|six|seven|eight|nine|shunya|ek|do|teen|chaar|char|paanch|panch|che|chheh|chhah|saat|sat|aath|ath|nau)\b/iu', $clean) ||
            preg_match('/(?:^|\s)(शून्य|ज़ीरो|जीरो|एक|वन|दो|टू|तीन|थ्री|चार|फोर|पांच|पाँच|फाइव|छह|छः|सिक्स|सात|सेवन|आठ|एट|नौ|नाइन|[०-९])(?:$|\s)/u', $clean) ||
            preg_match('/\b(cue|vee|gee|zee|zed|doubleu|aitch)\b/iu', $clean);

        if (!$hasDigitOrIdClue && str_contains($clean, ' ')) {
            return null;
        }

        // Devanagari letter names mapping to Latin uppercase
        $devanagariLetters = [
            'डबल्यू' => 'W', 'डब्लू' => 'W', 'डब्ल्यू' => 'W',
            'क्यू' => 'Q', 'क्यु' => 'Q', 'कियू' => 'Q',
            'आर' => 'R', 'अर' => 'R',
            'वी' => 'V', 'व्ही' => 'V', 'वे' => 'V', 'वि' => 'V',
            'जी' => 'G', 'ज़ी' => 'G',
            'एच' => 'H',
            'आई' => 'I',
            'जे' => 'J',
            'के' => 'K',
            'एल' => 'L',
            'एम' => 'M',
            'एन' => 'N',
            'ओ' => 'O',
            'पी' => 'P',
            'एस' => 'S',
            'टी' => 'T',
            'यू' => 'U', 'यु' => 'U',
            'एक्स' => 'X',
            'वाई' => 'Y', 'वाय' => 'Y',
            'ज़ेड' => 'Z', 'जेड' => 'Z',
            'ए' => 'A', 'ऐ' => 'A',
            'बी' => 'B',
            'सी' => 'C',
            'डी' => 'D',
            'ई' => 'E',
            'एफ' => 'F', 'फ़' => 'F', 'एफ़' => 'F',
        ];
        uksort($devanagariLetters, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        // Spelled English letter names
        $englishLetterNames = [
            'ay' => 'A', 'aye' => 'A', 'bee' => 'B', 'cee' => 'C', 'see' => 'C', 'sea' => 'C',
            'dee' => 'D', 'ee' => 'E', 'eff' => 'F', 'ef' => 'F', 'gee' => 'G', 'jee' => 'G',
            'aitch' => 'H', 'eitch' => 'H', 'eye' => 'I', 'jay' => 'J', 'kay' => 'K',
            'el' => 'L', 'ell' => 'L', 'em' => 'M', 'en' => 'N', 'oh' => 'O', 'pee' => 'P',
            'cue' => 'Q', 'queue' => 'Q', 'ar' => 'R', 'are' => 'R', 'arr' => 'R',
            'ess' => 'S', 'es' => 'S', 'tee' => 'T', 'tea' => 'T', 'you' => 'U', 'vee' => 'V',
            'doubleu' => 'W', 'ex' => 'X', 'why' => 'Y', 'zed' => 'Z', 'zee' => 'Z',
        ];

        // Digit word mapping
        $digitWords = [
            // English digit words
            'zero' => '0', 'one' => '1', 'two' => '2', 'three' => '3', 'four' => '4',
            'five' => '5', 'six' => '6', 'seven' => '7', 'eight' => '8', 'nine' => '9',

            // Hindi Devanagari digit words
            'शून्य' => '0', 'ज़ीरो' => '0', 'जीरो' => '0',
            'एक' => '1', 'वन' => '1',
            'दो' => '2', 'टू' => '2',
            'तीन' => '3', 'थ्री' => '3',
            'चार' => '4', 'फोर' => '4',
            'पांच' => '5', 'पाँच' => '5', 'फाइव' => '5',
            'छह' => '6', 'छः' => '6', 'सिक्स' => '6',
            'सात' => '7', 'सेवन' => '7',
            'आठ' => '8', 'एट' => '8',
            'नौ' => '9', 'नाइन' => '9',

            // Roman Hindi digit words
            'shunya' => '0', 'ek' => '1', 'do' => '2', 'teen' => '3', 'chaar' => '4', 'char' => '4',
            'paanch' => '5', 'panch' => '5', 'che' => '6', 'chheh' => '6', 'chhah' => '6',
            'saat' => '7', 'sat' => '7', 'aath' => '8', 'ath' => '8', 'nau' => '9',
        ];

        // Split by whitespace or commas/hyphens/dots
        $tokens = preg_split('/[\s,\.\-]+/u', $clean, -1, PREG_SPLIT_NO_EMPTY);
        $result = '';

        foreach ($tokens as $token) {
            $tTrim = trim($token);
            if ($tTrim === '') continue;
            $tLower = mb_strtolower($tTrim, 'UTF-8');

            // 1. Direct Devanagari letter names
            if (isset($devanagariLetters[$tTrim])) {
                $result .= $devanagariLetters[$tTrim];
                continue;
            }

            // 2. Compound Devanagari letter names (e.g. "क्यूआरवी" -> "QRV", "जीएबी" -> "GAB")
            if (preg_match('/^[\x{0900}-\x{097F}]+$/u', $tTrim)) {
                $remaining = $tTrim;
                $subRes = '';
                while ($remaining !== '') {
                    $matched = false;
                    foreach ($devanagariLetters as $key => $val) {
                        if (str_starts_with($remaining, $key)) {
                            $subRes .= $val;
                            $remaining = mb_substr($remaining, mb_strlen($key));
                            $matched = true;
                            break;
                        }
                    }
                    if (!$matched) {
                        break;
                    }
                }
                if ($remaining === '' && $subRes !== '') {
                    $result .= $subRes;
                    continue;
                }
            }

            // 3. English spelled letter names
            if (isset($englishLetterNames[$tLower])) {
                $result .= $englishLetterNames[$tLower];
                continue;
            }

            // 4. Digit words
            if (isset($digitWords[$tTrim])) {
                $result .= $digitWords[$tTrim];
                continue;
            }
            if (isset($digitWords[$tLower])) {
                $result .= $digitWords[$tLower];
                continue;
            }

            // 5. Single Latin character (A-Z)
            if (preg_match('/^[A-Za-z]$/', $tTrim)) {
                $result .= strtoupper($tTrim);
                continue;
            }

            // 6. Sequence of ASCII digits (e.g. "123456" or "10025")
            if (preg_match('/^\d+$/', $tTrim)) {
                $result .= $tTrim;
                continue;
            }

            // 7. Sequence of Latin letters (e.g. "QRV" or "GAB", 2-5 uppercase chars)
            if (preg_match('/^[A-Za-z]{2,5}$/', $tTrim)) {
                $result .= strtoupper($tTrim);
                continue;
            }

            // 8. Alphanumeric sequence (e.g. "GAB10025" or "QRV123")
            if (preg_match('/^[A-Za-z0-9_-]+$/', $tTrim)) {
                $result .= strtoupper($tTrim);
                continue;
            }

            // Unresolvable token: fail safely
            return null;
        }

        if ($result !== '' && preg_match('/^[A-Z0-9_-]{2,30}$/', $result)) {
            return $result;
        }

        return null;
    }

    /**
     * Format numeric value or identifier string for digit-by-digit voice synthesis.
     * Prevents text-to-speech engines from reading digit strings as Indian place-value numbers
     * (e.g. "123456" -> "one two three four five six" instead of "one lakh twenty-three thousand...").
     *
     * @param string $value
     * @param string $lang 'hi', 'hinglish', or 'en'
     * @return string
     */
    public static function formatDigitByDigitSpeech(string $value, string $lang = 'hi'): string
    {
        $val = trim($value);
        if ($val === '') return '';

        // Convert any Devanagari digits to 0-9
        $val = self::convertDevanagariDigits($val);

        $hindiDigits = [
            '0' => 'शून्य', '1' => 'एक', '2' => 'दो', '3' => 'तीन', '4' => 'चार',
            '5' => 'पाँच', '6' => 'छह', '7' => 'सात', '8' => 'आठ', '9' => 'नौ',
        ];

        $englishDigits = [
            '0' => 'zero', '1' => 'one', '2' => 'two', '3' => 'three', '4' => 'four',
            '5' => 'five', '6' => 'six', '7' => 'seven', '8' => 'eight', '9' => 'nine',
        ];

        $chars = mb_str_split($val);
        $formatted = [];

        foreach ($chars as $ch) {
            if (isset($englishDigits[$ch])) {
                if ($lang === 'hi') {
                    $formatted[] = $hindiDigits[$ch];
                } else {
                    $formatted[] = $englishDigits[$ch];
                }
            } elseif (preg_match('/^[A-Za-z]$/', $ch)) {
                $formatted[] = strtoupper($ch);
            } else {
                $formatted[] = $ch;
            }
        }

        return implode(' ', $formatted);
    }

    /**
     * Transliterate a Devanagari Hindi string to clean Roman / English characters.
     * Handles dictionary overrides, syllable analysis, matras, halant, and schwa deletion.
     */
    public static function transliterate(string $text, bool $isName = false): string
    {
        $text = self::convertDevanagariDigits($text);
        if (!self::hasDevanagari($text)) {
            return $isName ? self::formatNameCase($text) : $text;
        }

        // Split into words by whitespace / punctuation, preserving English words
        $words = preg_split('/(\s+|[.,!?:;"\'-]+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $output = '';

        foreach ($words as $word) {
            if (trim($word) === '' || preg_match('/^\s+$/u', $word) || preg_match('/^[.,!?:;"\'-]+$/u', $word)) {
                $output .= $word;
                continue;
            }

            // 1. Direct dictionary match
            if (isset(self::$knownWords[$word])) {
                $output .= self::$knownWords[$word];
                continue;
            }

            // If word has no Devanagari, keep as is
            if (!self::hasDevanagari($word)) {
                $output .= $word;
                continue;
            }

            // 2. Character-by-character Abugida Transliteration
            $transliteratedWord = self::transliterateWord($word);

            if ($isName) {
                $transliteratedWord = ucfirst($transliteratedWord);
            }

            $output .= $transliteratedWord;
        }

        $res = trim($output);
        return $isName ? self::formatNameCase($res) : $res;
    }

    /**
     * Transliterate a single Devanagari word using phonetic abugida logic.
     */
    protected static function transliterateWord(string $word): string
    {
        if (isset(self::$knownWords[$word])) {
            return self::$knownWords[$word];
        }

        $chars = mb_str_split($word);
        $len = count($chars);
        $res = '';

        for ($i = 0; $i < $len; $i++) {
            $char = $chars[$i];
            $nextChar = ($i + 1 < $len) ? $chars[$i + 1] : null;
            $prevChar = ($i > 0) ? $chars[$i - 1] : null;

            // Handle Nukta combination: e.g. क + ़ = क़
            if ($nextChar === '़' && isset(self::$consonants[$char . '़'])) {
                $char = $char . '़';
                $i++;
                $nextChar = ($i + 1 < $len) ? $chars[$i + 1] : null;
            }

            // 1. Independent Vowel
            if (isset(self::$vowels[$char])) {
                $res .= self::$vowels[$char];
                continue;
            }

            // 2. Dependent Vowel (Matra)
            if (isset(self::$matras[$char])) {
                $res .= self::$matras[$char];
                continue;
            }

            // 3. Halant / Virama (suppresses inherent 'a')
            if ($char === '्') {
                continue;
            }

            // 4. Anusvara / Candrabindu
            if ($char === 'ं' || $char === 'ँ') {
                $res .= 'n';
                continue;
            }

            // 5. Visarga
            if ($char === 'ः') {
                $res .= 'h';
                continue;
            }

            // 6. Consonant
            if (isset(self::$consonants[$char])) {
                $cons = self::$consonants[$char];

                // Check following character:
                // If followed by Halant '्', no vowel
                if ($nextChar === '्') {
                    $res .= $cons;
                    continue;
                }

                // If followed by Matra, no inherent 'a'
                if ($nextChar !== null && isset(self::$matras[$nextChar])) {
                    $res .= $cons;
                    continue;
                }

                // If at the end of the word, apply Hindi Schwa deletion (e.g. 'र' in 'कुमार' -> 'r', not 'ra')
                if ($i === $len - 1) {
                    $res .= $cons;
                    continue;
                }

                // Internal consonant: add inherent vowel 'a'
                $res .= $cons . 'a';
                continue;
            }

            // Non-Devanagari character
            $res .= $char;
        }

        // Post-processing cleanups
        $res = preg_replace('/aa$/', 'a', $res); // End-of-word aa -> a (e.g. "Abhaa" -> "Abha")
        $res = preg_replace('/([bcdfghjklmnpqrstvwxyz])a$/i', '$1', $res); // Clean trailing schwa

        return $res;
    }

    /**
     * Format a person's name with standard Title Casing.
     * E.g. "abhi kumar" -> "Abhi Kumar".
     */
    public static function formatNameCase(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name));
        $cased = array_map(fn($p) => ucfirst(strtolower($p)), $parts);
        return implode(' ', $cased);
    }

    /**
     * Parse phone / mobile numbers:
     * - Converts Devanagari numerals (०-९ -> 0-9).
     * - Parses spoken digit sequences ("ek do teen..." -> "123...").
     * - Preserves leading zeros.
     * - Extracts clean 7-15 digit string.
     */
    public static function normalizePhoneNumber(string $text): ?string
    {
        $clean = self::convertDevanagariDigits(trim($text));

        // 1. Direct digits in text
        $digitsOnly = preg_replace('/[^\d]/', '', $clean);
        if (strlen($digitsOnly) >= 7 && strlen($digitsOnly) <= 15) {
            return $digitsOnly;
        }

        // 2. Parse spoken digit sequences (e.g. "ek do teen chaar paanch chhah saat aath nau shunya")
        $words = preg_split('/[\s,-]+/u', mb_strtolower($clean));
        $parsedDigits = '';

        foreach ($words as $w) {
            $w = trim($w);
            if ($w === '') continue;

            if (isset(self::$digitWords[$w])) {
                $parsedDigits .= self::$digitWords[$w];
            } elseif (is_numeric($w)) {
                $parsedDigits .= $w;
            }
        }

        if (strlen($parsedDigits) >= 7 && strlen($parsedDigits) <= 15) {
            return $parsedDigits;
        }

        return !empty($digitsOnly) ? $digitsOnly : null;
    }

    /**
     * Parse numeric amounts (supporting Devanagari digits and Hindi spoken words:
     * "एक हजार पांच सौ" -> 1500, "बारह हजार" -> 12000, "पचास" -> 50).
     */
    public static function normalizeNumericAmount(string $text): ?float
    {
        $clean = self::convertDevanagariDigits(trim($text));

        // 1. Check for currency prefix/suffix with digits e.g. "$1500", "1500 dollars", "1500.50"
        if (preg_match('/(?:\$|rs|inr|usd|रु|रुपये)?\s*([\d,]+(?:\.\d+)?)/iu', $clean, $m)) {
            $numStr = str_replace(',', '', $m[1]);
            if (is_numeric($numStr)) {
                return (float) $numStr;
            }
        }

        // 2. Hindi number words (both Devanagari and Roman Hinglish)
        $lower = mb_strtolower($clean);

        // Normalize Devanagari number words to Roman
        $hindiWordsMap = [
            'एक' => 'ek', 'दो' => 'do', 'तीन' => 'teen', 'चार' => 'chaar', 'पांच' => 'paanch',
            'छह' => 'chhah', 'सात' => 'saat', 'आठ' => 'aath', 'नौ' => 'nau', 'दस' => 'das',
            'ग्यारह' => 'gyarah', 'बारह' => 'barah', 'तेरह' => 'terah', 'चौदह' => 'chaudah',
            'पंद्रह' => 'pandrah', 'सोलह' => 'solah', 'सत्रह' => 'satrah', 'अठारह' => 'atharah',
            'उन्नीस' => 'unnees', 'बीस' => 'bees', 'पच्चीस' => 'pachees', 'तीस' => 'tees',
            'पैंतीस' => 'paitees', 'चालीस' => 'chalis', 'पचास' => 'pachas', 'साठ' => 'saath',
            'सत्तर' => 'sattar', 'अस्सी' => 'assi', 'नब्बे' => 'nabbe',
            'सौ' => 'sau', 'डेढ़ सौ' => 'dedh sau', 'ढाई सौ' => 'dhai sau',
            'हजार' => 'hazaar', 'हज़ार' => 'hazaar', 'लाख' => 'lakh', 'करोड़' => 'crore',
        ];

        foreach ($hindiWordsMap as $dev => $rom) {
            $lower = str_replace($dev, $rom, $lower);
        }

        return self::parseHindiNumberExpression($lower);
    }

    /**
     * Parse Roman Hinglish numeric expression into float.
     * Examples: "barah hazaar" -> 12000, "ek hazaar paanch sau" -> 1500, "pachas" -> 50.
     */
    protected static function parseHindiNumberExpression(string $text): ?float
    {
        $units = [
            'shunya' => 0, 'zero' => 0,
            'ek' => 1, 'one' => 1,
            'do' => 2, 'two' => 2,
            'teen' => 3, 'three' => 3,
            'chaar' => 4, 'four' => 4,
            'paanch' => 5, 'five' => 5,
            'chhah' => 6, 'che' => 6, 'six' => 6,
            'saat' => 7, 'seven' => 7,
            'aath' => 8, 'eight' => 8,
            'nau' => 9, 'nine' => 9,
            'das' => 10, 'ten' => 10,
            'gyarah' => 11, 'barah' => 12, 'terah' => 13, 'chaudah' => 14,
            'pandrah' => 15, 'solah' => 16, 'satrah' => 17, 'atharah' => 18,
            'unnees' => 19, 'bees' => 20,
            'ikkees' => 21, 'baees' => 22, 'teees' => 23, 'chaubees' => 24,
            'pachees' => 25, 'chhabees' => 26, 'sattaees' => 27, 'atthaees' => 28,
            'untees' => 29, 'tees' => 30,
            'ikatees' => 31, 'battees' => 32, 'taiyntees' => 33, 'chautees' => 34,
            'paiyntees' => 35, 'chhattees' => 36, 'saintees' => 37, 'adhtees' => 38,
            'untalis' => 39, 'chalis' => 40,
            'iktalis' => 41, 'bayalis' => 42, 'tentalis' => 43, 'chawalis' => 44,
            'paitalis' => 45, 'chhiyalis' => 46, 'saintalis' => 47, 'adtalis' => 48,
            'unchas' => 49, 'pachas' => 50,
            'ikkyavan' => 51, 'bavan' => 52, 'tirepan' => 53, 'chawwan' => 54,
            'pachpan' => 55, 'chhappan' => 56, 'sattavan' => 57, 'atthavan' => 58,
            'unsath' => 59, 'saath' => 60,
            'iksath' => 61, 'baasath' => 62, 'tirsath' => 63, 'chaunsath' => 64,
            'painsath' => 65, 'chhiyasath' => 66, 'sadsath' => 67, 'adsath' => 68,
            'unhattar' => 69, 'sattar' => 70,
            'ikhattar' => 71, 'bahattar' => 72, 'tihattar' => 73, 'chauhattar' => 74,
            'pachhattar' => 75, 'chhihattar' => 76, 'sat-hattar' => 77, 'at-hattar' => 78,
            'unasi' => 79, 'assi' => 80,
            'ikyasi' => 81, 'byasi' => 82, 'tirasi' => 83, 'chaurasi' => 84,
            'pachasi' => 85, 'chhiyasi' => 86, 'satasi' => 87, 'athasi' => 88,
            'navasi' => 89, 'nabbe' => 90,
            'ikyanve' => 91, 'banve' => 92, 'tiranve' => 93, 'chauranve' => 94,
            'pachanve' => 95, 'chhiyanve' => 96, 'sattanve' => 97, 'atthanve' => 98,
            'ninyanve' => 99,
        ];

        // Special phrases
        if (str_contains($text, 'dedh sau')) return 150.0;
        if (str_contains($text, 'dhai sau')) return 250.0;
        if (str_contains($text, 'dedh hazaar')) return 1500.0;
        if (str_contains($text, 'dhai hazaar')) return 2500.0;

        $words = preg_split('/[\s,-]+/u', $text);
        $total = 0;
        $currentGroup = 0;
        $matchedAny = false;

        foreach ($words as $w) {
            $w = trim($w);
            if ($w === '') continue;

            if (isset($units[$w])) {
                $currentGroup += $units[$w];
                $matchedAny = true;
            } elseif ($w === 'sau' || $w === 'hundred') {
                $currentGroup = ($currentGroup === 0 ? 1 : $currentGroup) * 100;
                $matchedAny = true;
            } elseif ($w === 'hazaar' || $w === 'hazar' || $w === 'thousand') {
                $total += ($currentGroup === 0 ? 1 : $currentGroup) * 1000;
                $currentGroup = 0;
                $matchedAny = true;
            } elseif ($w === 'lakh' || $w === 'lac') {
                $total += ($currentGroup === 0 ? 1 : $currentGroup) * 100000;
                $currentGroup = 0;
                $matchedAny = true;
            } elseif ($w === 'crore') {
                $total += ($currentGroup === 0 ? 1 : $currentGroup) * 10000000;
                $currentGroup = 0;
                $matchedAny = true;
            } elseif (is_numeric($w)) {
                $currentGroup += (float) $w;
                $matchedAny = true;
            }
        }

        $total += $currentGroup;

        return $matchedAny ? (float) $total : null;
    }
}
