<template>
    <div v-show="isActive">
        <slot></slot>
    </div>
</template>

<script>
export default {
    name: 'TimelineItem',
    props: {
        month: String,
        year: String,
        dateRange: []
    },
    data() {
        return {
            isTimelineItem: true,
            isActive: false
        }
    },
    methods: {
        activate() {
            this.isActive = true;
        },
        deactivate() {
            this.isActive = false;
        }
    }
}
</script>

