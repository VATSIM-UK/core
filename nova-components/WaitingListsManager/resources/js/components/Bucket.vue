<template>
    <div>
        <heading class="mb-6 py-3 px-6" v-text="title" />
        <p class="flex flex-col justify-center text-center p-2" v-if="numberOfAccounts < 1">
            There are no accounts assigned to this 'bucket'.
        </p>

        <div v-if="loaded && numberOfAccounts >= 1">
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
                        <span v-if="account.notes" @click="changeNote(account)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="cursor-pointer fill-current text-70 hover:text-primary">
                                <path class="heroicon-ui" d="M6 2h9a1 1 0 0 1 .7.3l4 4a1 1 0 0 1 .3.7v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2zm9 2.41V7h2.59L15 4.41zM18 9h-3a2 2 0 0 1-2-2V4H6v16h12V9zm-2 7a1 1 0 0 1-1 1H9a1 1 0 0 1 0-2h6a1 1 0 0 1 1 1zm0-4a1 1 0 0 1-1 1H9a1 1 0 0 1 0-2h6a1 1 0 0 1 1 1zm-5-4a1 1 0 0 1-1 1H9a1 1 0 1 1 0-2h1a1 1 0 0 1 1 1z"/>
                            </svg>
                        </span>
                        <span v-else @click="changeNote(account)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="cursor-pointer fill-current text-70 hover:text-primary">
                                <path class="heroicon-ui" d="M6 2h9a1 1 0 0 1 .7.3l4 4a1 1 0 0 1 .3.7v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2zm9 2.41V7h2.59L15 4.41zM18 9h-3a2 2 0 0 1-2-2V4H6v16h12V9z"/>
                            </svg>
                        </span>
                    </td>
                    <td>
                        <span class="inline-block rounded-full w-2 h-2"
                              :class="{ 'bg-success': account.atcHourCheck, 'bg-danger': !account.atcHourCheck }"
                        ></span>
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
                    <td>
                        <flag-indicator
                            v-for="flag in account.flags"
                            :key="flag.pivot.id"
                            :flag="flag"
                            @changeFlag="changeFlag"
                        ></flag-indicator>
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
        </div>
    </div>

</template>

<script>
    export default {
        name: "Bucket",

        props: ['accounts', 'title'],

        data() {
            return {
                loaded: true
            }
        },

        methods: {
            removeAccount(account) {
                this.$emit('removeAccount', { account: account })
            },

            deferAccount(account) {
                this.$emit('deferAccount', { account: account })
            },

            activeAccount(account) {
                this.$emit('activeAccount', { account: account })
            },

            changeNote(account) {
                this.$emit('changeNote', { account: account })
            },

            changeFlag(flag) {
                // check to see if the flag is automated or not.
                if (!flag.endorsement_id) {
                    this.$emit('changeFlag', flag)
                }
            }
        },

        computed: {
            numberOfAccounts() {
                if (this.accounts) return this.$props.accounts.length
            },
        },
    }
</script>