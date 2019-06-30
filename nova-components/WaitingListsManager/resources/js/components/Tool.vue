<template>
    <div>
        <heading class="mb-6" v-if="activeBucket">Eligible Waiting List (EWL)</heading>
        <heading class="mb-6 mt-6" v-else>Master Waiting List (MWL)</heading>
        <loading-view :loading="!loaded">
            <p class="flex flex-col justify-center text-center p-2" v-if="numberOfAccounts < 1">
                There are no accounts assigned to this 'bucket'.
            </p>

            <div class="overflow-hidden overflow-x-auto -my-3 -mx-6" v-if="loaded && numberOfAccounts >= 1">
                <table cellpadding="0" cellspacing="0" data-testid="resource-table" class="table w-full">
                    <thead>
                    <tr>
                        <th class="text-left">Position</th>
                        <th class="text-left">Name</th>
                        <th class="text-left">CID</th>
                        <th class="text-left">Added On</th>
                        <th class="text-left">Notes</th>
                        <th class="text-left">Hour Check</th>
                        <th>Status Change</th>
                        <th>Flags</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(account, index) in accounts" :class="{ 'opacity-50': !account.status.default }">
                        <td><span class="font-semibold">{{ index + 1 }}</span></td>
                        <td>
                            <div class="flex items-center">
                                <p>{{ account.name }}</p>
                            </div>
                        </td>
                        <td>{{ account.id }}</td>
                        <td>{{ this.moment(account.created_at.date).format("MMMM Do YYYY") }}</td>
                        <td>
                            <span v-if="account.notes" @click="openNotesModal(account)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="cursor-pointer fill-current text-70 hover:text-primary">
                                    <path class="heroicon-ui" d="M6 2h9a1 1 0 0 1 .7.3l4 4a1 1 0 0 1 .3.7v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2zm9 2.41V7h2.59L15 4.41zM18 9h-3a2 2 0 0 1-2-2V4H6v16h12V9zm-2 7a1 1 0 0 1-1 1H9a1 1 0 0 1 0-2h6a1 1 0 0 1 1 1zm0-4a1 1 0 0 1-1 1H9a1 1 0 0 1 0-2h6a1 1 0 0 1 1 1zm-5-4a1 1 0 0 1-1 1H9a1 1 0 1 1 0-2h1a1 1 0 0 1 1 1z"/>
                                </svg>
                            </span>
                            <span v-else @click="openNotesModal(account)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="cursor-pointer fill-current text-70 hover:text-primary">
                                    <path class="heroicon-ui" d="M6 2h9a1 1 0 0 1 .7.3l4 4a1 1 0 0 1 .3.7v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2zm9 2.41V7h2.59L15 4.41zM18 9h-3a2 2 0 0 1-2-2V4H6v16h12V9z"/>
                                </svg>
                            </span>
                        </td>
                        <td>
                            <span class="inline-block rounded-full w-2 h-2"
                                  :class="{ 'bg-success': account.atcHourCheck, 'bg-danger': !account.atcHourCheck }"
                                  @click="openFlagChangeModal(flag.pivot.id)"></span>
                        </td>
                        <td>
                            <div class="flex justify-around">
                                <button class="btn btn-sm btn-outline" v-if="account.status.name === 'Active'"
                                    @click="deferAccount(account.id)">
                                    Defer
                                </button>
                                <button class="btn btn-sm btn-outline" v-else
                                    @click="activeAccount(account.id)">
                                    Active
                                </button>
                            </div>

                        </td>
                        <td >
                            <div v-for="flag in account.flags" class="flex-row">
                                <p class="text-center">
                                    <span class="mr-1">{{ flag.name }}</span>
                                    <span class="inline-block rounded-full w-2 h-2 cursor-pointer"
                                          :class="{ 'bg-success': flag.pivot.value, 'bg-danger': !flag.pivot.value }"
                                          @click="openFlagChangeModal(flag.pivot.id)"></span>
                                </p>
                            </div>
                        </td>
                        <td>
                            <div class="flex justify-around">
                                <button class="cursor-pointer text-70 hover:text-primary mr-3" 
                                        @click="removeAccount(account.id)">
                                    <icon type="delete" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <portal to="modals">
                    <transition name="fade">
                        <confirm-flag-change-modal
                                v-if="flagConfirmModalOpen"
                                @confirm="confirmFlagChange"
                                @close="closeFlagChangeModal"
                        />

                    </transition>

                </portal>

                <portal to="modals">
                    <transition name="fade">
                        <text-input-modal
                            v-if="notesModalOpen"
                            @confirm="createNote"
                            @close="closeNotesModal"
                            :account="selectedAccount"
                        />
                    </transition>
                </portal>
            </div>
        </loading-view>
    </div>
</template>

<script>
    import { EventBus } from '../eventBus'
    export default {
        props: ['resourceName', 'resourceId', 'field'],

        data() {
            return {
                loaded: false,
                accounts: {},
                position: 0,
                flagConfirmModalOpen: false,
                notesModalOpen: false,
                selectedFlag: null,
                selectedAccount: null,
                activeBucket: this.field.activeBucket
            }
        },

        mounted() {
            this.loadAccounts()

            // required to detect any changes in the other buckets which might be present on the page.
            EventBus.$on('list-changed', this.loadAccounts)
        },

        computed: {
            numberOfAccounts() {
                if (this.loaded) return Object.keys(this.accounts).length
            },
        },

        methods: {
            loadAccounts() {

                if (!this.activeBucket) {
                    axios.get(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}`)
                        .then(response => {
                                this.accounts = response.data.data;
                                this.loaded = true;
                            }
                        );
                } else {
                    axios.get(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/active/index`)
                        .then(response => {
                                this.accounts = response.data.data;
                                this.loaded = true;
                            }
                        );
                }

            },

            getHourCheck(check) {
                return (check ? "Y" : "N")
            },

            removeAccount(account) {
                axios.post(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/remove`, { account_id: account })
                    .then(response => {
                        EventBus.$emit('list-changed')
                    }
                );
            },

            // Inactive as not currently needed.
            promoteAccount(account) {
                axios.post(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/promote`, { account_id: account })
                    .then(response => {
                        EventBus.$emit('list-changed')
                    }
                );
            },

            // Inactive as not currently needed.
            demoteAccount(account) {
                axios.post(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/demote`, { account_id: account })
                    .then(response => {
                        EventBus.$emit('list-changed')
                    }
                );
            },

            deferAccount(account) {
                axios.patch(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/defer`, { account_id: account })
                    .then(response => {
                        EventBus.$emit('list-changed')
                    }
                );
            },

            activeAccount(account) {
                axios.patch(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/active`, { account_id: account })
                    .then(response => {
                        EventBus.$emit('list-changed')
                    }
                );
            },

            openFlagChangeModal(selected) {
                this.selectedFlag = selected
                this.flagConfirmModalOpen = true
            },

            closeFlagChangeModal() {
                this.flagConfirmModalOpen = false
            },

            openNotesModal(selected) {
                this.selectedAccount = selected
                this.notesModalOpen = true
            },

            closeNotesModal() {
                this.notesModalOpen = false
            },

            confirmFlagChange(id) {
                console.log(id)
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
