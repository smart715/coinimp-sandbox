<template>
    <div class="row">
        <div class="col-12">
            <ul class="timeline-dates d-none d-xl-flex justify-content-between">
                <li class="date text-center d-inline-block" :class="{ active: isSelected(index) }"
                    v-for="(item, index) in timelineItems"
                    :key="index"
                    @click="setCurrentItem(index)">
                    <span class="year d-block">{{ item.year }}</span>
                    <span class="month d-block">{{ item.month }}</span>
                </li>
            </ul>
        </div>
        <div class="col-md-12 d-flex d-xl-none justify-content-center">
            <ul class="timeline-dates mobile">
                <li class="date arrow-left text-center d-inline-block" @click="previousItem">
                    <font-awesome-layers class="fa-4x">
                        <font-awesome-icon :icon="['fas','angle-left']"></font-awesome-icon>
                    </font-awesome-layers>
                </li>
                <li class="date text-center d-inline-block">
                    <span class="year mobile d-block">{{ selectedItem.year }}</span><br>
                    <span class="month mobile d-block">{{ selectedItem.month }}</span>
                </li>
                <li class="date arrow-right text-center d-inline-block" @click="nextItem">
                    <font-awesome-layers class="fa-4x">
                        <font-awesome-icon :icon="['fas','angle-right']"></font-awesome-icon>
                    </font-awesome-layers>
                </li>
            </ul>
        </div>
        <div class="col-12 d-none d-xl-block">
            <div class="progress progress-timeline">
                <div class="progress-bar" role="progressbar" :style="{ width: progress + '%'}" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
        <div class="col-12 p-5 justify-content-center">
            <slot></slot>
        </div>
    </div>
</template>

<script>

import _ from 'lodash';
import { FontAwesomeIcon, FontAwesomeLayers } from '@fortawesome/vue-fontawesome';

export default {
    name: 'Timeline',
    props: { currentDate: String },
    components: {
        FontAwesomeIcon,
        FontAwesomeLayers
    },
    mounted() {
        this.timelineItems = this.$children.filter(child => child.isTimelineItem);
        let initialIndex = _.findIndex(this.timelineItems, item => this.isCurrentDate(item.dateRange));
        this.setCurrentItem(initialIndex);
    },
    data() {
        return {
            selectedItem: {
                index: 0,
                month: '',
                year: ''
            },
            timelineItems: []
        }
    },
    watch: {
        selectedItem(newItem, previousItem) {
            this.timelineItems[previousItem.index].deactivate();
            this.timelineItems[newItem.index].activate();
        }
    },
    computed: {
        progress() {
            let percentage = [0, 24, 47, 70, 100];
            return percentage[this.selectedItem.index];
        }
    },
    methods: {
        setCurrentItem(index) {
            if (index >= 0 && index <= (this.timelineItems.length - 1))
                this.selectedItem = {
                    index: index,
                    month: this.timelineItems[index].month,
                    year: this.timelineItems[index].year
                }
        },
        isSelected(index) {
            return index == this.selectedItem.index;
        },
        isCurrentDate(dateRange) {
            return true;
        },
        previousItem() {
            this.setCurrentItem(this.selectedItem.index - 1);
        },
        nextItem() {
            this.setCurrentItem(this.selectedItem.index + 1);
        }
    }
}
</script>
