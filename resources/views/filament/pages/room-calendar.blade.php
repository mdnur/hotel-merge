<x-filament::page>
    <div wire:ignore>
        <div id="roomCalendar" style="min-height: 750px;"></div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let calendarEl = document.getElementById('roomCalendar');

            let rooms = @json(
                $this - > getRooms() - > map(fn($room) => [
                    'id' => $room - > id
                    , 'title' => $room - > room_no
                , ])
            );

            /* Card Rooms as events */




            let calendar = new FullCalendar.Calendar(calendarEl, {
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',

                initialView: 'resourceTimelineMonth'
                , initialDate: '2026-01-01'
                , height: 'auto',

                resourceAreaHeaderContent: 'Rooms',

                resources: rooms,

                events: [{
                        resourceId: '101'
                        , title: 'A-12 · New · Paid'
                        , start: '2026-01-02'
                        , end: '2026-01-15'
                        , backgroundColor: '#FDBA4D'
                    , }
                    , {
                        resourceId: '103'
                        , title: 'A-45 · Confirmed · Paid'
                        , start: '2026-01-07'
                        , end: '2026-01-08'
                        , backgroundColor: '#8BC34A'
                    , }
                    , {
                        resourceId: '103'
                        , title: 'A-45 · Confirmed · Paid'
                        , start: '2026-01-07'
                        , end: '2026-01-08'
                        , backgroundColor: '#8BC34A'
                    , }


                    , {
                        resourceId: '202'
                        , title: 'A-28 · Checked Out'
                        , start: '2026-01-04'
                        , end: '2026-01-18'
                        , backgroundColor: '#BDBDBD'
                    , }
                , ]
            , });

            calendar.render();
        });

    </script>
    @endpush
</x-filament::page>
