@component('mail::message')
{{ __('The project ":name" has reached its estimated time.', ['name' => $projectName]) }}

@component('mail::button', ['url' => $projectUrl])
{{ __('Go to project') }}
@endcomponent

@endcomponent
