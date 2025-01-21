<x-layout>
    <x-slot name="title">Coordinatorr</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <h3 class="text-center mb-4">Planned Activation:</h3>
            <form action="/add_planned_activation" method="post">
                @csrf
                <div class="form-group">
                    <label for="eventcallsignid">Event Callsign:</label>
                    <select class="form-control" id="eventcallsignid" name="eventcallsignid">
                        @foreach($callsigns as $callsign)
                        <option value="{{$callsign->id}}">{{$callsign->call}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="activator_callsign">Your callsign:</label>
                    <input name="activator_callsign" class="form-control" type="text" placeholder="Enter your personal callsign here...">
                </div>
                <div class="form-group">
                    <label for="start">Start (UTC):</label>
                    <input name="start" class="form-control" type="datetime-local">
                </div>
                <div class="form-group">
                    <label for="end">End (UTC):</label>
                    <input name="end" class="form-control" type="datetime-local">
                </div>
                @if($appmode == 'SINGLEOP')
                <input type="hidden" id="band_id" name="band_id" value=""/>
                <input type="hidden" id="mode_id" name="mode_id" value=""/>
                @endif
                @if($appmode == 'MULTIOPBAND')
                <div class="form-group">
                    <label for="band_id">Band:</label>
                    <select class="form-control" id="band_id" name="band_id">
                        @foreach($bands as $band)
                        <option value="{{$band->id}}" {{ $band->start == 14 ? 'selected' : '' }}>{{$band->band}}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" id="mode_id" name="mode_id" value=""/>
                @endif
                @if($appmode == 'MULTIOPMODE')
                <div class="form-group">
                    <label for="band_id">Band:</label>
                    <select class="form-control" id="band_id" name="band_id">
                        @foreach($bands as $band)
                        <option value="{{$band->id}}" {{ $band->start == 14 ? 'selected' : '' }}>{{$band->band}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="mode_id">Mode:</label>
                    <select class="form-control" id="mode_id" name="mode_id">
                        @foreach($modes as $mode)
                        <option value="{{$mode->id}}" {{ $mode->mode == 'VOICE' ? 'selected' : '' }}>{{$mode->mode}}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="text-center">
                    <input type="submit" class="btn btn-primary" value="Plan Activation">
                </div>
            </form>
        </div>
        @if($planned_activations->count() > 0)
        <h3 class="text-center mb-4" style="margin-top: 30px;">Planned activations (sorted by start date):</h3>
        <div style="text-align: center"><a href="/planned_activations/calendar"><button class="btn btn-primary">Calendar</button></a></div>
        <div style="margin-bottom: 80px;">
            @foreach($planned_activations as $activation)
            <div class="container mt-5 section" style="background-color: rgba(255, 255, 255, 0.5);">
                <table class="table table-bordered table-hover table-dark" style="margin-bottom: 5px;">
                    <thead class="thead-light">
                        <tr>
                            <th>Eventcallsign / From</th>
                            <th>Activatorcall / To</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $activation->callsign->call }}</td>
                            <td>{{ $activation->activator->call }}</td>
                        </tr>
                        <tr>
                            <td>{{ $activation->start->setTimezone('UTC') }} UTC</td>
                            <td>{{ $activation->end->setTimezone('UTC') }} UTC</td>
                        </tr>
                        @if($appmode != 'SINGLEOP')
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                Band: {{ $activation->band == null ? 'unknown' : $activation->band->band }}<br>
                                @if($appmode == 'MULTIOPMODE')
                                Mode: {{ $activation->mode == null ? 'unknown' : $activation->mode->mode }}
                                @endif
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                
                <div style="text-align:right;">
                    <a href="/planned_activation/{{ $activation->id }}/delete" style="text-align: right;"><button class="btn btn-danger">Delete Planned Activation</button></a>
                </div>
                
            </div>
            @endforeach
        </div>
        @else
        <div style="margin-bottom: 80px;">
        <!-- Dummy Diff als Abstandshalter für Mobile Landscape -->
        </div>
        @endif
    </x-slot>

</x-layout>
    
