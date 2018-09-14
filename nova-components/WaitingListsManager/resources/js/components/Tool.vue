<template>
    <div>
        <loading-view :loading="!loaded">
            <p class="flex flex-col justify-center text-center p-2" v-if="numberOfAccounts < 1">
                There are no accounts assigned to this waiting list.
            </p>

            <div class="overflow-hidden overflow-x-auto border-2 border-40" v-if="loaded && numberOfAccounts >= 1">
                <table cellpadding="0" cellspacing="0" data-testid="resource-table" class="table w-full">
                    <thead>
                    <tr>
                        <th class="text-left">Position</th>
                        <th class="text-left">Name</th>
                        <th class="text-left">CID</th>
                        <th class="text-left">Added On</th>
                        <th class="text-left">Current Status</th>
                        <th class="text-left">ATC Hour Check</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="account in accounts" :class="{ 'opacity-50': !account.status.retains_position }">
                        <td><span class="font-semibold">{{ account.position }}</span></td>
                        <td>{{ account.name }}</td>
                        <td>{{ account.id }}</td>
                        <td>{{ this.moment(account.created_at.date).format("MMMM Do YYYY") }}</td>
                        <td>{{ account.status.name }}</td>
                        <td v-bind:class="{ 'text-green': account.atcHourCheck }"><span>{{ getHourCheck(account.atcHourCheck) }}</span></td>
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
            }
        }
    }
</script>
