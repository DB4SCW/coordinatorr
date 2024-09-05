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
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                Since:<br>
                                {{ $activation->start->setTimezone('Europe/Berlin') }} local time <br> {{ $activation->start->setTimezone('UTC') }} UTC
                                @if($activation->hamalert_spot_datetime != null)
                                <br>Last spot: {{ $activation->start->setTimezone('UTC') }}<br>{{ $activation->hamalert_frequency }} MHz {{ $activation->hamalert_mode }}
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
    
