<template>
    <div class="warning font_size_s" :class="additional_class">
        <slot></slot>
    </div>
</template>

<script>
    export default {
        name: "Warning",
        props: {
            additional_class: {
                type: String,
                default: ''
            }
        }
    }
</script>

<style scoped>
    .warning {
        padding: 15px;
        text-align: center;
        color: #856404;
        background: #fff3cd;
        border: 2px solid #ffeeba;
        border-radius: 5px;
        animation: changeColor 1.3s infinite alternate;
    }

    @keyframes changeColor {
        from {
            color: #856404;
        }
        to {
            color: #F0E4C1;
        }
    }
</style>