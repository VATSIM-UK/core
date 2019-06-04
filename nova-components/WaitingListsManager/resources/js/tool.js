Nova.booting((Vue, router) => {
    Vue.component('waiting-lists-manager', require('./components/Tool'));
    Vue.component('confirm-flag-change-modal', require('./components/ConfirmFlagChangeModal'))
})
