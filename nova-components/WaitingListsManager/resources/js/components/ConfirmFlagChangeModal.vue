<template>
    <modal
        data-testid="confirm-action-modal"
        tabindex="-1"
        role="dialog"
        @modal-close="handleClose"
        class-whitelist="btn-primary"
    >
        <form
                autocomplete="off"
                @keydown="handleKeydown"
                @submit.prevent.stop="handleConfirm"
                class="bg-white rounded-lg shadow-lg overflow-hidden w-action"
        >
            <div>
                <heading :level="2" class="border-b border-40 py-8 px-8">Flag Change Confirmation</heading>

                <p class="text-80 px-8 my-8">
                    Are you sure you want to change this flag?
                </p>
            </div>

            <div class="bg-30 px-6 py-3 flex">
                <div class="flex items-center ml-auto">
                    <button
                            dusk="cancel-action-button"
                            type="button"
                            @click.prevent="handleClose"
                            class="btn text-80 font-normal h-9 px-3 mr-3 btn-link"
                    >
                        {{ __('Cancel') }}
                    </button>

                    <button
                            ref="runButton"
                            dusk="flag-action-button"
                            type="submit"
                            class="btn btn-default btn-primary"
                    >
                        <span>{{ __('Change Flag') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </modal>
</template>

<script>
    export default {
        name: "ConfirmFlagChangeModal",

        mounted () {
            this.$refs.runButton.focus()
        },

        methods: {
            /**
             * Stop propogation of input events unless it's for an escape or enter keypress
             */
            handleKeydown(e) {
                if (['Escape', 'Enter'].indexOf(e.key) !== -1) {
                    return
                }

                e.stopPropagation()
            },

            /**
             * Execute the selected action.
             */
            handleConfirm() {
                this.$emit('confirm')
            },

            /**
             * Close the modal.
             */
            handleClose() {
                this.$emit('close')
            },
        }
    }
</script>

<style scoped>

</style>