<template>
    <div>
        <loading-view :loading="!loaded">
            <p class="flex flex-col justify-center text-center p-2" v-if="numberOfAccounts < 1">
                There are no accounts assigned to this waiting list.
            </p>

            <div class="relative -py-3 -px-6" v-if="loaded && numberOfAccounts >= 1">
                <div class="overflow-hidden overflow-x-auto relative">
                    <table cellpadding="0" cellspacing="0" data-testid="resource-table" class="table w-full">
                        <thead>
                        <tr>
                            <th class="text-left">Name</th>
                            <th class="text-left">CID</th>
                            <th class="text-left">Added On</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="account in accounts">
                            <td>{{ account.name }}</td>
                            <td>{{ account.id }}</td>
                            <td>{{ account.created_at }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
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
            axios.get(`/nova-vendor/waiting-lists-manager/accounts/${this.resourceId}`)
                .then(response => {
                    this.accounts = response.data;
                    this.loaded = true;
                });
        },

        computed: {
            numberOfAccounts() {
                if (this.loaded) return Object.keys(this.accounts).length
            }
        }
    }
</script>
