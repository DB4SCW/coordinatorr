<x-layout>
    <x-slot name="title">DB4SCW Eventcoordination</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <form action="/add_activation" method="post">
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
                    <input type="submit" class="btn btn-primary" value="Start Activation">
                </div>
            </form>
        </div>
        @if($current_activations->count() > 0)
        <h3 class="text-center mb-4" style="margin-top: 30px;">Current activations:</h3>
        <div style="margin-bottom: 80px;">
            @foreach($current_activations as $activation)
            <div class="container mt-5 section" style="background-color: rgba(255, 255, 255, 0.5);">
                <table class="table table-bordered table-hover table-dark" style="margin-bottom: 5px;">
                    <thead class="thead-light">
                        <tr>
                            <th>Eventcallsign</th>
                            <th>Activatorcall</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $activation->callsign->call }}</td>
                            <td>{{ $activation->activator->call }}</td>
                        </tr>
                        @if($appmode != 'SINGLEOP')
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                Band: {{ $activation->band->band }}<br>
                                @if($appmode == 'MULTIOPMODE')
                                Mode: {{ $activation->mode->mode ?? 'unknown' }}
                                @endif
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                Since:<br>
                                {{ $activation->start->setTimezone('Europe/Berlin') }} local time <br> {{ $activation->start->setTimezone('UTC') }} UTC
                                @if($activation->hamalert_spot_datetime != null)
                                <br>Last spot: <br>{{ $activation->start->setTimezone('UTC') }} UTC<br>{{ $activation->hamalert_frequency }} MHz {{ $activation->hamalert_mode }}
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="text-align:right;">
                    <a href="/end_activation/{{ $activation->id }}" style="text-align: right;"><button class="btn btn-danger">End Activation</button></a>
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
    
