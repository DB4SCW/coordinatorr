<x-layout>
    <x-slot name="title">Coordinatorr</x-slot>

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
            <div class="container mt-5 section">
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
                                <br>Last spot: <br>{{ $activation->hamalert_spot_datetime->setTimezone('UTC') }} UTC<br>{{ $activation->hamalert_frequency }} MHz {{ $activation->hamalert_mode }}
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="text-align:right;">
                    @if(config('app.db4scw_end_functions_javascript'))
                    <button class="btn btn-danger" onclick="showConfirmEndActivationModal({{ $activation->id }})">End Activation</button>
                    @else
                    <a href="/end_activation/{{ $activation->id }}" style="text-align: right;"><button class="btn btn-danger">End Activation</button></a>
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
        <div class="modal fade" id="confirmEndofActivationModal" tabindex="-1" role="dialog" aria-labelledby="confirmEndofActivationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dark" role="document">
              <div class="modal-content">
                <div class="modal-header modal-dark">
                  <h5 class="modal-title" id="confirmEndofActivationModalLabel">Confirm End of Activation?</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" id="message">
                  Are you sure you want to end this activation?
                </div>
                <div class="modal-footer">
                  <form id="confirmEndofActivationForm" method="post" action="/end_activation">
                    @csrf
                    
                    <input type="hidden" id="activationIdmodal" name="activationId" value="">
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
            let activationidtoend; // Variable to store the activation ID to be end
        
            // Function to show the confirmation modal dialog
            function showConfirmEndActivationModal(activationId) {
                activationidtoend = activationId; // Store the upload ID in the variable
                $('#activationIdmodal').val(activationId); //set id to hidden input field
                document.getElementById('message').innerHTML = "".concat('Are you sure you want to end this activation?');
                $('#confirmEndofActivationModal').modal('show'); // Show the modal
            }
        </script>
    </x-slot>

</x-layout>
    
