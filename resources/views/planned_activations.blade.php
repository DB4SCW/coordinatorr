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
            <div class="container mt-5 section">
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
                    @if(config('app.db4scw_end_functions_javascript'))
                    <button class="btn btn-danger" onclick="showConfirmDeleteActivationModal({{ $activation->id }})">Delete Planned Activation</button>
                    @else
                    <a href="/planned_activation/{{ $activation->id }}/delete" style="text-align: right;"><button class="btn btn-danger">Delete Planned Activation</button></a>
                    @endif
                </div>
                
            </div>
            @endforeach
        </div>
        @else
        <div style="margin-bottom: 80px;">
        <!-- Dummy Diff als Abstandshalter für Mobile Landscape -->
        </div>
        @endif

        <!-- Modal dialog to confirm ending of activation -->
        <div class="modal fade" id="confirmDeletionofActivationModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeletionofActivationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dark" role="document">
              <div class="modal-content">
                <div class="modal-header modal-dark">
                  <h5 class="modal-title" id="confirmDeletionofActivationModalLabel">Confirm Deletion of Planned Activation?</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" id="message">
                  Are you sure you want to delete this planned activation?
                </div>
                <div class="modal-footer">
                  <form id="confirmDeletionofActivationForm" method="post" action="/planned_activation/delete">
                    @csrf
                    
                    <input type="hidden" id="activationIdmodal" name="plannedactivationId" value="">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">End Activation</button>
                  </form>
                </div>
              </div>
            </div>
        </div>
    </x-slot>

    <x-slot name="scripts">
        <script>
            let activationidtodelete; // Variable to store the activation ID to be deleted
        
            // Function to show the confirmation modal dialog
            function showConfirmDeleteActivationModal(activationId) {
                activationidtodelete = activationId; // Store the upload ID in the variable
                $('#activationIdmodal').val(activationId); //set id to hidden input field
                document.getElementById('message').innerHTML = "".concat('re you sure you want to delete this planned activation?');
                $('#confirmDeletionofActivationModal').modal('show'); // Show the modal
            }
        </script>
    </x-slot>

</x-layout>
    
