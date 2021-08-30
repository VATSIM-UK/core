Nova.booting((Vue, router) => {
    Vue.component('waiting-lists-manager', require('./components/Tool').default),
    Vue.component('confirm-flag-change-modal', require('./components/ConfirmFlagChangeModal').default),
    Vue.component('offer-training-place-modal', require('./components/OfferTrainingPlaceModal').default),
    Vue.component('text-input-modal', require('./components/TextInputModal').default),
    Vue.component('flag-indicator', require('./components/FlagIndicator').default),
    Vue.component('note-indicator', require('./components/NoteIndicator').default),
    Vue.component('bucket', require('./components/Bucket').default);
})
