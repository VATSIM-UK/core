Nova.booting((Vue, router) => {
    Vue.component('waiting-lists-manager', require('./components/Tool'));
    Vue.component('confirm-flag-change-modal', require('./components/ConfirmFlagChangeModal'))
    Vue.component('text-input-modal', require('./components/TextInputModal'))
    Vue.component('flag-indicator', require('./components/FlagIndicator'))
    Vue.component('note-indicator', require('./components/NoteIndicator'))
    Vue.component('bucket', require('./components/Bucket'))
})
