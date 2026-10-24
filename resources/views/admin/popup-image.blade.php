@extends('admin.layouts.main')
@section('title', 'Dashboard Popup Image')
@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-image bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('Dashboard Popup Image') }}</h5>
                            <span>{{ __('Manage the announcement / promotional popup image shown on Member Dashboard upon login') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ url('admin/dashboard') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ __('Admin') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Popup Image') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        @if (session()->has('uploadMsg'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ik ik-check-circle mr-2"></i> {{ session('uploadMsg') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session()->has('delMsg'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="ik ik-alert-circle mr-2"></i> {{ session('delMsg') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <!-- Upload & Settings Column -->
            <div class="col-lg-5 col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="font-weight-bold"><i class="ik ik-upload-cloud mr-1 text-primary"></i> {{ __('Upload / Update Popup Image') }}</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.popupImageUpdate') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">{{ __('Select Image') }} <span class="text-danger">*</span></label>
                                <div class="custom-file">
                                    <input type="file" name="image" class="custom-file-input @error('image') is-invalid @enderror" id="popupImageInput" accept="image/*" onchange="previewImage(this)">
                                    <label class="custom-file-label" for="popupImageInput" id="fileLabel">{{ __('Choose Image File (JPG, PNG, WEBP)') }}</label>
                                </div>
                                @error('image')
                                    <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                @enderror
                                <small class="text-muted mt-1 d-block">
                                    <i class="ik ik-info mr-1"></i> Recommended format: JPG, PNG, WEBP (Max: 5MB). High quality banners (e.g. 800x600, 1080x1080, or 1200x800).
                                </small>
                            </div>

                            <!-- Live preview container -->
                            <div class="form-group mb-3 text-center" id="previewContainer" style="display: none;">
                                <label class="font-weight-bold d-block text-left">{{ __('Selected Image Preview') }}</label>
                                <div style="border: 2px dashed #007bff; border-radius: 8px; padding: 8px; background: #f8f9fa;">
                                    <img id="imagePreview" src="#" alt="Preview" style="max-height: 200px; max-width: 100%; border-radius: 6px; object-fit: contain;">
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold">{{ __('Popup Title / Heading (Optional)') }}</label>
                                <input type="text" class="form-control" name="title" value="{{ old('title', $popupTitle) }}" placeholder="e.g. Special Announcement / New Staking Pool">
                                @error('title')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold">{{ __('Action Button Link / URL (Optional)') }}</label>
                                <input type="url" class="form-control" name="link" value="{{ old('link', $popupLink) }}" placeholder="e.g. https://t.me/demo or member/staking">
                                <small class="text-muted"><i class="ik ik-link mr-1"></i> Optional link if you want members to click through when seeing the popup.</small>
                                @error('link')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold d-block">{{ __('Popup Status') }}</label>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="statusActive" name="status" value="1" class="custom-control-input" {{ $popupStatus == '1' ? 'checked' : '' }}>
                                    <label class="custom-control-label text-success font-weight-bold" for="statusActive">
                                        <i class="ik ik-check-circle"></i> {{ __('Active (Show to Members)') }}
                                    </label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="statusInactive" name="status" value="0" class="custom-control-input" {{ $popupStatus == '0' ? 'checked' : '' }}>
                                    <label class="custom-control-label text-muted font-weight-bold" for="statusInactive">
                                        <i class="ik ik-slash"></i> {{ __('Inactive (Hide)') }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group text-right mb-0">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm px-4">
                                    <i class="ik ik-save mr-1"></i> {{ __('Save & Publish') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Current Active Image Card Column -->
            <div class="col-lg-7 col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="font-weight-bold"><i class="ik ik-eye mr-1 text-info"></i> {{ __('Current Dashboard Popup Preview') }}</h3>
                        <div>
                            @if (!empty($popupImage) && file_exists(public_path('uploads/' . $popupImage)))
                                @if ($popupStatus == '1')
                                    <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 13px;">
                                        <i class="ik ik-check-circle mr-1"></i> {{ __('Active on Member Dashboard') }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary px-3 py-2 font-weight-bold" style="font-size: 13px;">
                                        <i class="ik ik-slash mr-1"></i> {{ __('Currently Inactive / Hidden') }}
                                    </span>
                                @endif
                            @else
                                <span class="badge badge-warning px-3 py-2 font-weight-bold" style="font-size: 13px;">
                                    <i class="ik ik-alert-circle mr-1"></i> {{ __('No Image Uploaded') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if (!empty($popupImage) && file_exists(public_path('uploads/' . $popupImage)))
                            <div class="text-center p-3 rounded" style="background: #1e293b; border: 1px solid #334155; position: relative;">
                                @if (!empty($popupTitle))
                                    <h5 class="text-white font-weight-bold mb-3">{{ $popupTitle }}</h5>
                                @endif
                                <img src="{{ asset('uploads/' . $popupImage) }}" alt="Dashboard Popup Banner" class="img-fluid rounded shadow" style="max-height: 380px; object-fit: contain; width: 100%;">
                                
                                @if (!empty($popupLink))
                                    <div class="mt-3">
                                        <a href="{{ $popupLink }}" target="_blank" class="btn btn-outline-info btn-sm">
                                            <i class="ik ik-external-link mr-1"></i> {{ __('Test Action Link') }}: {{ $popupLink }}
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 pt-2 border-top">
                                <div>
                                    <a href="{{ route('admin.popupImageToggleStatus') }}" class="btn {{ $popupStatus == '1' ? 'btn-warning' : 'btn-success' }} font-weight-bold mr-2">
                                        <i class="ik {{ $popupStatus == '1' ? 'ik-eye-off' : 'ik-eye' }} mr-1"></i>
                                        {{ $popupStatus == '1' ? __('Set to Inactive (Hide)') : __('Set to Active (Show)') }}
                                    </a>
                                    <a href="{{ asset('uploads/' . $popupImage) }}" target="_blank" class="btn btn-outline-primary">
                                        <i class="ik ik-maximize-2 mr-1"></i> {{ __('View Full Size') }}
                                    </a>
                                </div>
                                <div>
                                    <a href="{{ route('admin.popupImageDelete') }}" class="btn btn-outline-danger font-weight-bold" onclick="return confirm('Are you sure you want to delete this popup image?');">
                                        <i class="ik ik-trash-2 mr-1"></i> {{ __('Delete Image') }}
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="ik ik-image text-muted" style="font-size: 64px; opacity: 0.4;"></i>
                                </div>
                                <h5 class="font-weight-bold text-dark">{{ __('No Popup Image Set') }}</h5>
                                <p class="mb-0">{{ __('Upload an image using the form on the left. It will be instantly displayed to all members when they log into their dashboard.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            function previewImage(input) {
                var preview = document.getElementById('imagePreview');
                var container = document.getElementById('previewContainer');
                var label = document.getElementById('fileLabel');

                if (input.files && input.files[0]) {
                    var file = input.files[0];
                    label.innerText = file.name;
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        container.style.display = 'block';
                    }

                    reader.readAsDataURL(file);
                } else {
                    container.style.display = 'none';
                }
            }
        </script>
    @endpush
@endsection
