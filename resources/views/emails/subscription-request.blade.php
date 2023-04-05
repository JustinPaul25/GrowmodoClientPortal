@component('mail::layout')
@slot('header')
    @component('mail::header', ['url' => 'https://hub.growmodo.dev'])
        Growmodo Portal API
    @endcomponent
@endslot

<b>Hello Roman,</b>

<p>{{$message ? $message : 'Change of Plan request'}}</p>

<p>
  Talents: <b>{{$subscription_talent->label}}</b><br>
  Plan: <b>{{$subscription_billing->billed_label}}</b><br>
  Starts On: <b>{{$starts_on}}</b>
</p>

<p>
  {{$organization->title}} by {{$user->firstname}} {{$user->lastname}}
</p>

<p>
  Regards,<br>
  Growmodo Portal API
</p>

@slot('footer')
    @component('mail::footer')
        © 2022 Growmodo Portal API. All rights reserved.
    @endcomponent
@endslot
@endcomponent
