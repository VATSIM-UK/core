<template>
    <div class="flex-row">
        <p class="text-center">
            <span class="mr-1">{{ flag.name }}</span>
            <span
                class="inline-block rounded-full w-2 h-2 cursor-pointer"
                :class="{ 'bg-success': flag.pivot.value, 'bg-danger': !flag.pivot.value }"
                @click="changeFlag(flag.pivot.id)"
            ></span>
        </p>
    </div>
</template>

<script>
    export default {
        name: "FlagIndicator",

        props: ['flag'],

        methods: {
            changeFlag(flag) {
                this.$emit('changeFlag', { flag: flag })
            }
        }
    }
</script>