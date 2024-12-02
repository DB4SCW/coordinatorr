<x-layout>
    <x-slot name="title">DB4SCW Eventcoordination Adminpanel</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <form action="/admin/add_activator" method="post">
                @csrf
                <div class="form-group">
                    <label for="activator_callsign">Activator:</label>
                    <input name="activator_callsign" class="form-control" type="text" placeholder="Activator callsign here...">
                </div>
                <div class="text-center">
                    <input type="submit" class="btn btn-primary" value="Add activator">
                </div>
            </form>
        </div>
        <div class="container mt-5">
            <form action="/admin/add_callsign" method="post">
                @csrf
                <div class="form-group">
                    <label for="event_callsign">Eventcallsign:</label>
                    <input name="event_callsign" class="form-control" type="text" placeholder="Event callsign here...">
                </div>
                <div class="text-center">
                    <input type="submit" class="btn btn-primary" value="Add event callsign">
                </div>
            </form>
        </div>
        <h3 class="text-center mb-4" style="margin-top: 30px;">Coordinatorr mode:</h3>
        <div style="margin-bottom: 80px;">
            <div class="container mt-5">
                <form action="/admin/switch_mode" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="mode" style="color:red;">DANGER: Changing this will delete all current activations and upcoming planned activations!</label>
                        <select class="form-control" name="mode">
                            @foreach ($modes as $mode)
                            <option value="{{ $mode->option }}" {{ env('COORDINATORR_MODE', 'SINGLEOP') == $mode->option ? 'selected' : '' }}>{{ $mode->option }} -> {{ $mode->description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-center">
                        <input type="submit" class="btn btn-primary" value="Change Coordinatorr mode">
                    </div>
                </form>
            </div>
        </div>
        <h3 class="text-center mb-4" style="margin-top: 30px;">Activators:</h3>
        <div style="margin-bottom: 80px;">
            <div class="container mt-5 section" style="background-color: rgba(255, 255, 255, 0.5);">
                <table class="table table-bordered table-hover table-dark" style="margin-bottom: 5px;">
                    <thead class="thead-light">
                        <tr>
                            <th>Activatorcall</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activators as $activator)
                        <tr>
                            <td>{{ $activator->call }}</td>
                            <td style="text-align: center;">
                                <a href="/activator/{{ $activator->call }}/remove"><button class="btn btn-danger">Remove Activator</button></a>
                                @if($activator->locked)
                                <a href="/activator/{{ $activator->call }}/lock"><button class="btn btn-success">Unlock Activator</button></a>
                                @else
                                <a href="/activator/{{ $activator->call }}/lock"><button class="btn btn-warning">Lock Activator</button></a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <h3 class="text-center mb-4" style="margin-top: 30px;">Eventcalls:</h3>
        <div style="margin-bottom: 80px;">
            <div class="container mt-5 section" style="background-color: rgba(255, 255, 255, 0.5);">
                <table class="table table-bordered table-hover table-dark" style="margin-bottom: 5px;">
                    <thead class="thead-light">
                        <tr>
                            <th>Eventcalls</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eventcalls as $eventcall)
                        <tr>
                            <td>{{ $eventcall->call }}</td>
                            <td style="text-align: center;">
                                <a href="/callsign/{{ $eventcall->call }}/remove"><button class="btn btn-danger">Remove Eventcallsign</button></a>
                                @if($eventcall->hidden)
                                <a href="/callsign/{{ $eventcall->call }}/hide"><button class="btn btn-success">Reveal Eventcallsign</button></a>
                                @else
                                <a href="/callsign/{{ $eventcall->call }}/hide"><button class="btn btn-warning">Hide Eventcallsign</button></a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div style="margin-bottom: 80px;">
        <!-- Dummy Diff als Abstandshalter für Mobile Landscape -->
        </div>
    </x-slot>

</x-layout>

