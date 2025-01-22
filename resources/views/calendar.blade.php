<x-layout>
    <x-slot name="title">Coordinator</x-slot>

    <x-slot name="styles">
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    </x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <h1>Calendar:</h1>
            <div style="text-align: center"><a href="/planned_activations"><button class="btn btn-primary">Back to planned activations</button></a></div>
            <div id="calendar"></div>
            <script src='fullcalendar/core/index.global.js'></script>
            <script src='fullcalendar/core/locales-all.global.js'></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var calendarEl = document.getElementById('calendar');
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'timeGridWeek',
                    events: '/planned_activations/export',
                    eventClick: function(info) {
                        // don't let the browser navigate
                        info.jsEvent.preventDefault(); 

                        //open url in new window
                        if (info.event.url) {
                            window.open(info.event.url);
                        }
                    },
                    lang: 'en',
                    firstDay: 1,
                    height: 'auto',
                    timeFormat: 'H(:mm)'
                    });
                    calendar.render();
                    calendar.setOption('locale', 'de');
                });
            </script>
        </div>

        <div style="margin-bottom: 80px;">
            <!-- Dummy Diff als Abstandshalter für Mobile Landscape -->
        </div>
    </x-slot>
</x-layout>
