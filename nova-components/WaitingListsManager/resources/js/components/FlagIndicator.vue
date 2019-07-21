<template>
    <div class="flex-row">
        <p class="text-center">
            <span class="mr-1" :class="{ 'italic': automated }">{{ flag.name }}</span>
            <span
                class="inline-block rounded-full w-2 h-2"
                :class="{ 'bg-success': flag.pivot.value, 'bg-danger': !flag.pivot.value, 'cursor-pointer': !automated }"
                @click="changeFlag(flag)"
            ></span>
        </p>
    </div>
</template>

<script>
    export default {
        name: "FlagIndicator",

        props: ['flag'],

        computed: {
            automated() {
                return !!this.flag.endorsement_id;
            }
        },

        methods: {
            changeFlag(flag) {
                this.$emit('changeFlag', flag)
            }
        }
    }
</script>