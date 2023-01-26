<template>
    <div>
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
                    <th class="text-left" v-if="isAtcList">Hour Check</th>
                    <th>Status Change</th>
                    <th class="text-left" v-if="isAtcList">Theory Exam</th>
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
                    <td>{{ createdFormatted(account.created_at) }}</td>
                    <td>
                        <note-indicator
                            :account="account"
                            @changeNote="changeNote"/>
                    </td>
                    <td v-if="isAtcList">
                        <boolean-indicator :value="account.atcHourCheck" />
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
                        <boolean-indicator :value="account.theory_exam_passed" />
                    <td>
                        <flag-indicator
                            v-for="flag in account.flags"
                            :key="flag.pivot.id"
                            :flag="flag"
                            @changeFlag="changeFlag"
                        />
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
    import BooleanIndicator from "./BooleanIndicator";
    export default {
        name: "Bucket",

        props: ['accounts', 'type'],

        components: { BooleanIndicator },

        data() {
            return {
                loaded: true
            }
        },

        methods: {
            removeAccount(account) {
                this.$emit('removeAccount', { account })
            },

            deferAccount(account) {
                this.$emit('deferAccount', { account })
            },

            activeAccount(account) {
                this.$emit('activeAccount', { account })
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

            isAtcList() {
                return this.type === 'atc'
            }
        },
    }
</script>
