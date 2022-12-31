<template>
    <div class="flex-row">
        <p class="text-center">
            <span class="mr-1" :class="{ 'italic': automated }">{{ flag.name }}</span>
            <boolean-indicator
                :value="flag.pivot.value"
                :class="{ 'cursor-pointer': !automated }"
                @click.native="changeFlag(flag)"
            />
        </p>
    </div>
</template>

<script>
    import BooleanIndicator from "./BooleanIndicator";
    export default {
        name: "FlagIndicator",

        props: ['flag'],

        components: { BooleanIndicator },

        computed: {
            automated() {
                return !!this.flag.endorsement_id;
            }
        },

        methods: {
            changeFlag(flag) {
                if (this.automated) return;

                this.$emit('changeFlag', flag)
            }
        }
    }
</script>
