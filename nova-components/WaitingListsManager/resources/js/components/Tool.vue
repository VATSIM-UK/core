<template>
    <div>
        <loading-card :loading="!loaded">
            <div class="py-3 mx-6">
                <heading class="mb-6">
                Eligible Waiting List (EWL)
                </heading>
                <p>Note: Theory exam status does <strong>not</strong> influence overall eligibility in this list.
                It is included for reference purposes.</p>
            </div>

            <bucket
                :accounts="eligibleAccounts"
                :type="type"
                @removeAccount="removeAccount"
                @deferAccount="deferAccount"
                @activeAccount="activeAccount"
                @changeNote="openNotesModal"
                @changeFlag="openFlagChangeModal"
            />
            <heading class="mb-6 py-3 px-6">
                Master Waiting List (MWL)
            </heading>
            <bucket
                :accounts="normalAccounts"
                :type="type"
                @removeAccount="removeAccount"
                @deferAccount="deferAccount"
                @activeAccount="activeAccount"
                @changeNote="openNotesModal"
                @changeFlag="openFlagChangeModal"
            />
        </loading-card>

        <portal to="modals">
            <confirm-flag-change-modal
                v-if="flagConfirmModalOpen"
                @confirm="confirmFlagChange"
                @close="closeFlagChangeModal"
            />
            <text-input-modal
                v-if="notesModalOpen"
                @confirm="createNote"
                @close="closeNotesModal"
                :account="selectedAccount"
            />
        </portal>
    </div>
</template>

<script>
    import { EventBus } from '../eventBus'
    export default {
        props: ['resourceName', 'resourceId', 'panel'],

        data() {
            return {
                loaded: false,
                normalAccounts: null,
                eligibleAccounts: null,
                position: 0,
                flagConfirmModalOpen: false,
                notesModalOpen: false,
                selectedFlag: null,
                selectedAccount: null
            }
        },

        mounted() {
            this.loadAccounts()

            // required to detect any changes in the other buckets which might be present on the page.
            EventBus.$on('list-changed', this.loadAccounts)
        },

        computed: {
            type() {
                return this.panel.fields[0].type
            }
        },

        methods: {
            loadAccounts() {
                axios.get(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}`)
                    .then(response => {
                            this.normalAccounts = response.data.data;
                            this.loaded = true;
                        }
                    )

                axios.get(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/active/index`)
                    .then(response => {
                            this.eligibleAccounts = response.data.data;
                            this.loaded = true;
                        }
                    )

            },

            getHourCheck(check) {
                return (check ? "Y" : "N")
            },

            removeAccount(payload) {
                Nova.request().post(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/remove`, { account_id: payload.account})
                    .then(response => {
                        this.$toasted.show('Account removed from waiting list.', { type: 'success'})
                        EventBus.$emit('list-changed')
                    })
            },

            deferAccount(payload) {
                axios.patch(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/defer`, { account_id: payload.account })
                    .then(response => {
                        EventBus.$emit('list-changed')
                    }
                )
            },

            activeAccount(payload) {
                Nova.request().patch(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/active`, { account_id: payload.account })
                    .then(response => {
                        EventBus.$emit('list-changed')
                    }
                )
            },

            openFlagChangeModal(selected) {
                this.selectedFlag = selected.pivot.id
                this.flagConfirmModalOpen = true
            },

            closeFlagChangeModal() {
                this.flagConfirmModalOpen = false
            },

            openNotesModal(selected) {
                this.selectedAccount = selected.account
                this.notesModalOpen = true
            },

            closeNotesModal() {
                this.notesModalOpen = false
            },

            confirmFlagChange() {
                Nova.request().patch(`/nova-vendor/waiting-lists-manager/flag/${this.selectedFlag}/toggle`).then(() => {
                    // close the modal dialog
                    this.closeFlagChangeModal()
                    // show a success message
                    this.$toasted.show('Flag changed successfully!', { type: 'success'})
                    // refresh the data
                    EventBus.$emit('list-changed')
                })
            },

            createNote (payload) {
                Nova.request().patch(`/nova-vendor/waiting-lists-manager/notes/${this.selectedAccount.pivot_id}/create`, {
                    notes: payload.value
                }).then((response) => {
                    this.closeNotesModal()

                    this.$toasted.show(response.data.success, { type: 'success' })

                    EventBus.$emit('list-changed')
                })
            }
        }
    }
</script>
