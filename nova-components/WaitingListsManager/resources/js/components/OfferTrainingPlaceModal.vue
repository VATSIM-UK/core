<template>
    <modal
        role="dialog"
    >
        <loading-card :loading="loading">
            <form
                class="bg-white roudned-lg shadow-lg overflow-hidden w-action-fields"
                @submit.prevent.stop="handleSubmit"
            >
                <heading :level="2" class="mb-2 p-8">Offer Training Place</heading>
                <main class="p-8">
                    <div class="flex items-center">
                        <label for="" class="w-1/4 font-normal text-80">Position</label>
                        <select class="form-control form-select" v-model="selectedPosition">
                            <option value="default" disabled selected>Select Position</option>
                            <option v-for="place in places" :key="place.id" :value="place.id">
                                {{ place.callsign }}
                            </option>
                        </select>
                    </div>
                </main>
                <footer class="bg-30 px-6 py-3 flex justify-end">
                    <button
                        type="reset"
                        @click.prevent="handleClose"
                        class="btn text-80 font-normal h-9 px-3 mr-3 btn-link"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="btn btn-default btn-primary"
                        :disabled="buttonDisabled"
                    >
                        {{ __('Submit') }}
                    </button>
                </footer>
            </form>

        </loading-card>
    </modal>
</template>

<script>
export default {
    name: "OfferTrainingPlaceModal",

    props: ['waitingList'],

    data() {
        return {
            places: [],
            selectedPosition: 'default',
            loading: true
        }
    },

    computed: {
        buttonDisabled() {
            return this.selectedPosition === 'default' || this.selectedPosition === null;
        }
    },

    created() {
        this.loading = true;
        Nova.request().get(`/nova-vendor/waiting-lists-manager/waitingLists/${this.waitingList}/available-places`)
            .then(response => {
                this.places = response.data.places;
                this.loading = false;
            })
    },

    methods: {
        handleSubmit() {
            this.$emit('submit', { position: this.selectedPosition });
        },
        handleClose() {
            this.$emit('close')
        }
    }
}
</script>
