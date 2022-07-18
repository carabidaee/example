<template>
    <div v-if="loading" class="loader" >
        <img :src="require('../../../assets/spinner.gif')" class="loader-icon" alt=""/>
    </div>
</template>

<script>
    export default {
        name: "Loader",
        props: {
            loading: {
                type: Boolean,
                default: true
            }
        },
        created() {
            this.changeOverflow();
        },
        updated() {
            this.changeOverflow();
        },
        methods: {
            changeOverflow() {
                document.body.style.overflow = this.loading ? 'hidden' : 'visible';
            }
        }
    }
</script>

<style scoped>
    .loader {
        position: fixed;
        top: 121px;
        width: 100%;
        height: calc(100% - 121px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    .loader-icon {
        height: 153px;
        width: 150px;
    }
</style>