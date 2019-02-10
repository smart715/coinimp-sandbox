<template>
    <div class="row ico-progress p-4 mb-lg-4">
        <div class="col-md-12 d-flex justify-content-between percentage-progress">
            <span v-if="isPreSale" class="mb-2">Soft-cap goal: {{ numberFormat(totalImp, ' ') }} IMP</span>
            <span v-else class="mb-2">Hard-cap goal: {{ numberFormat(totalImp, ' ') }} IMP</span>
            <span class="mb-2"> $ {{ numberFormat(totalImpUsd, '.') }}</span>
        </div>
        <div class="col-md-12 text-center">
            <div class="progress">
                <div
                        role="progressbar"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        class="progress-bar success"
                        :style="{ width: percentage + '%' }"
                >
                </div>
            </div>
        </div>
        <div class="col-md-12 text-center count-progress">
            <span class="mt-2">{{ numberFormat(soldImp, ' ') }}</span>
            <strong>IMP have been sold and still counting!</strong>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import number_format from 'locutus/php/strings/number_format';

    export default {
        name: 'TokenSaleProgressCounter',
        data () {
            return {
                soldImp: 0,
                totalImp: 0,
                percentage: 0,
                totalImpUsd: 0
            }
        },
        props: {
            url: String,
            isPreSale: String,
            impUsdRate: String
        },
        mounted: function () {
            this.pullData();
            setInterval(this.pullData, 60000);
        },
        methods:{
            pullData: function () {
                axios.get(this.url).then(response => {
                    this.soldImp = response.data.soldImp;
                    this.totalImp = response.data.totalImp;
                    this.totalImpUsd = response.data.totalImp * parseFloat(this.impUsdRate);
                    this.percentage = (this.soldImp / this.totalImp) * 100;
                });
            },
            numberFormat: function(value, separator) {
                return number_format(value, 0 , '.', separator);
            }
        }
    }
</script>
