@if (count($activity->changes['after']) == 1)
    {{ $activity->user->displayName() }} updated the {{ key($activity->changes['after']) }} of the project
@else
    {{ $activity->user->displayName() }} updated the project
@endif