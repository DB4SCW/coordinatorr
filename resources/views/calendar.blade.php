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
                        // Don't let the browser navigate
                        info.jsEvent.preventDefault(); 

                        // Open URL in new window
                        if (info.event.url) {
                            window.open(info.event.url);
                        }
                    },
                    locale: 'en-gb', // British English for 24-hour format
                    firstDay: 1, // Monday as the first day of the week
                    height: 'auto',
                    eventTimeFormat: { // Event times in 24-hour format
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    },
                    slotLabelFormat: { // Time slots in 24-hour format
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    },
                    columnHeaderText: function(date) {
                        // Custom format for column headers: "Wed 22.01"
                        const weekday = date.toLocaleDateString('en-US', { weekday: 'short' }); // Force English weekday
                        const day = String(date.getDate()).padStart(2, '0'); // Add leading zero
                        const month = String(date.getMonth() + 1).padStart(2, '0'); // Add leading zero
                        return `${weekday} ${day}.${month}`; // Return in "DDD DD.MM" format
                    }
                });
                calendar.render();
            });

            </script>
        </div>

        <div style="margin-bottom: 80px;">
            <!-- Dummy Diff als Abstandshalter für Mobile Landscape -->
        </div>
    </x-slot>
</x-layout>
