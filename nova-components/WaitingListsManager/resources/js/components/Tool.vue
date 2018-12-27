<template>
    <div>
        <loading-view :loading="!loaded">
            <p class="flex flex-col justify-center text-center p-2" v-if="numberOfAccounts < 1">
                There are no accounts assigned to this waiting list.
            </p>

            <div class="overflow-hidden overflow-x-auto -my-3 -mx-6" v-if="loaded && numberOfAccounts >= 1">
                <table cellpadding="0" cellspacing="0" data-testid="resource-table" class="table w-full">
                    <thead>
                    <tr>
                        <th class="text-left">Position</th>
                        <th class="text-left">Name</th>
                        <th class="text-left">CID</th>
                        <th class="text-left">Added On</th>
                        <th class="text-left">Current Status</th>
                        <th class="text-left">ATC Hour Check</th>
                        <th>Status Change</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(account, index) in accounts" :class="{ 'opacity-50': !account.status.default }">
                        <td><span class="font-semibold">{{ account.position }}</span></td>
                        <td>{{ account.name }}</td>
                        <td>{{ account.id }}</td>
                        <td>{{ this.moment(account.created_at.date).format("MMMM Do YYYY") }}</td>
                        <td>{{ account.status.name }}</td>
                        <td v-bind:class="{ 'text-green': account.atcHourCheck }"><span>{{ getHourCheck(account.atcHourCheck) }}</span></td>
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
                        <td>
                            <div class="flex justify-around">
                                
                                <button class="cursor-pointer text-70 hover:text-primary mr-3" 
                                        @click="promoteAccount(account.id)" v-if="account.id !== accounts[0].id">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path class="heroicon-ui fill-current" d="M13 5.41V21a1 1 0 0 1-2 0V5.41l-5.3 5.3a1 1 0 1 1-1.4-1.42l7-7a1 1 0 0 1 1.4 0l7 7a1 1 0 1 1-1.4 1.42L13 5.4z"/></svg>
                                </button>

                                <button class="cursor-pointer text-70 hover:text-primary mr-3" 
                                        @click="demoteAccount(account.id)" v-if="account.id !== accounts[accounts.length -1].id">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path class="heroicon-ui fill-current" d="M11 18.59V3a1 1 0 0 1 2 0v15.59l5.3-5.3a1 1 0 0 1 1.4 1.42l-7 7a1 1 0 0 1-1.4 0l-7-7a1 1 0 0 1 1.4-1.42l5.3 5.3z"/></svg>
                                </button>

                                <button class="cursor-pointer text-70 hover:text-primary mr-3" 
                                        @click="removeAccount(account.id)">
                                    <icon type="delete" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </loading-view>
    </div>
</template>

<script>
    export default {
        props: ['resourceName', 'resourceId', 'field'],

        data() {
            return {
                loaded: false,
                accounts: {},
                position: 0,
            }
        },

        mounted() {
            this.loadAccounts()
        },

        computed: {
            numberOfAccounts() {
                if (this.loaded) return Object.keys(this.accounts).length
            },
        },

        methods: {
            loadAccounts() {
                axios.get(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}`)
                    .then(response => {
                        this.accounts = response.data.data;
                        this.loaded = true;
                    }
                );
            },

            getHourCheck(check) {
                return (check ? "Y" : "N")
            },

            removeAccount(account) {
                axios.post(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/remove`, { account_id: account })
                    .then(response => {
                        this.loadAccounts();
                    }
                );
            },

            promoteAccount(account) {
                axios.post(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/promote`, { account_id: account })
                    .then(response => {
                        this.loadAccounts();
                    }
                );
            },

            demoteAccount(account) {
                axios.post(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/demote`, { account_id: account })
                    .then(response => {
                        this.loadAccounts();
                    }
                );
            },

            deferAccount(account) {
                axios.patch(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/defer`, { account_id: account })
                    .then(response => {
                        this.loadAccounts()
                    }
                );
            },

            activeAccount(account) {
                axios.patch(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}/active`, { account_id: account })
                    .then(response => {
                        this.loadAccounts()
                    }
                );
            }
        }
    }
</script>
