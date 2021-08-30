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
                    <th class="text-left" v-if="showHourChecker">Hour Check</th>
                    <th>Status Change</th>
                    <th>Flags</th>
                    <th v-if="eligibleList">Training Place</th>
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
                    <td>{{ createdFormatted(account.created_at) }}</td>
                    <td>
                        <note-indicator
                            :account="account"
                            @changeNote="changeNote"/>
                    </td>
                    <td v-if="showHourChecker">
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
                    <td v-if="eligibleList">
                        <button class="btn btn-sm btn-outline" @click="offerPlace(account.id)">Offer Training</button>
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

        props: ['accounts', 'title', 'type', 'eligibleList'],

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

            offerPlace(account) {
                this.$emit('offerPlace', { account: account })
            },

            changeNote(account) {
                this.$emit('changeNote', account)
            },

            changeFlag(flag) {
                // check to see if the flag is automated or not.
                if (!flag.endorsement_id) {
                    this.$emit('changeFlag', flag)
                }
            },

            createdFormatted (created_at) {
                return moment(created_at).format("MMMM Do YYYY")
            }
        },

        computed: {
            numberOfAccounts() {
                if (this.accounts) return this.$props.accounts.length
            },

            showHourChecker () {
                return this.type === 'atc'
            }
        },
    }
</script>
