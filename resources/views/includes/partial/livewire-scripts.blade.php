@can('upload documents')
    <script data-navigate-once>
        /* Handle File upload*/
        let draggedFile = false;
        document.addEventListener("dragenter", function (e){
            let dt = e.dataTransfer;
            let dragHasFile = dt.types && (dt.types.indexOf ? dt.types.indexOf('Files') != -1 : dt.types.contains('Files'));

            if(!draggedFile &&  dragHasFile) {
                draggedFile = true;
                Livewire.dispatch('modal.open', { component: 'modals.documents-uploader' })
            }
        });

        document.addEventListener("dragleave", function (e){
            if (!e.fromElement && draggedFile){
                Livewire.dispatch('modal.close', { component: 'modals.documents-uploader' })
                draggedFile = false;
            }
        });
    </script>
@endcan

<script data-navigate-once>
    document.addEventListener('livewire:initialized', function () {
        @if(session('vehicle_not_selected'))
        Livewire.dispatch('modal.open', { component: 'modals.select-vehicle' })
        @endif

        Livewire.on('flashNotification', (event) => {
            let type = event.type ? event.type : 'success';
            $.NotificationApp.send(event.heading, event.message, "bottom-right", "rgba(0,0,0,0.2)", type);
        });

        @if(user()->hasUnreadAnnouncements())
            Livewire.dispatch('modal.open', { component: 'modals.announcement-modal' })
        @endif
    });

</script>

@yield('livewire-scripts')
