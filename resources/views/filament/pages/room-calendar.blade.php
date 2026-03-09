<x-filament::page>
    <div wire:ignore>
        <div id="roomCalendar" style="min-height:750px;"></div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('roomCalendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives'
                , initialView: 'resourceTimelineMonth'
                , initialDate: '2026-01-01'
                , height: 'auto'
                , resourceAreaHeaderContent: 'Rooms',

                resources: @json($resources)
                , events: @json($events)
            , });

            calendar.render();
        });

    </script>
    @endpush
</x-filament::page>
