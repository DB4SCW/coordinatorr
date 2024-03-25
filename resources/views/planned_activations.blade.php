<x-layout>
    <x-slot name="title">DB4SCW Eventcoordination</x-slot>

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
                <div class="text-center">
                    <input type="submit" class="btn btn-primary" value="Plan Activation">
                </div>
            </form>
        </div>
        @if($planned_activations->count() > 0)
        <h3 class="text-center mb-4" style="margin-top: 30px;">Planned activations (sorted by start date):</h3>
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
    
