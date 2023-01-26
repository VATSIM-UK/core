<template>
    <span
        class="inline-block rounded-full w-2 h-2"
        :class="indicatorClass"
    />
</template>

<script>
export default {
    name: "BooleanIndicator",

    props: {
        value: {
            type: Boolean,
            required: true
        }
    },

    computed: {
        indicatorClass() {
            return {
                'bg-success': this.value,
                'bg-danger': !this.value
            }
        }
    }
}
</script>
