<x-filament::page>
    <div class="p-4 bg-white shadow rounded-xl">
        <div id="room-calendar" style="min-height: 400px;"></div>
    </div>

    @push('scripts')
    <!-- FULLCALENDAR + SCHEDULER (CRITICAL) -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/resource-timeline@6.1.11/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const calendarEl = document.getElementById('room-calendar');

            if (!calendarEl) {
                console.error('Calendar element not found');
                return;
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'resourceTimelineMonth'
                , height: 'auto',

                headerToolbar: {
                    left: 'prev,next today'
                    , center: 'title'
                    , right: ''
                },

                resourceAreaHeaderContent: 'Rooms',

                resources: [{
                        id: 1
                        , title: '101 | Eco Deluxe'
                    }
                    , {
                        id: 2
                        , title: '102 | Eco Deluxe'
                    }
                    , {
                        id: 3
                        , title: '201 | Deluxe Couple'
                    }
                    , {
                        id: 4
                        , title: '301 | Premium Couple'
                    }
                , ],

                events: [{
                        id: 1
                        , resourceId: 1
                        , title: 'A-101'
                        , start: '2026-01-20'
                        , end: '2026-01-23'
                        , backgroundColor: '#22c55e'
                    , }
                    , {
                        id: 2
                        , resourceId: 3
                        , title: 'A-201'
                        , start: '2026-01-22'
                        , end: '2026-01-26'
                        , backgroundColor: '#f59e0b'
                    , }
                , ]
            , });

            calendar.render();
        });

    </script>
    @endpush
</x-filament::page>
