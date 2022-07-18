<template>
    <transition name="slide-fade">
        <transition-group v-if="show" name="slide-fade" tag="div" :class="wrapper_class">
            <div v-for="(error, name) in errors" :key="`key-${name}`" :class="elementClass">
                {{ error }}
            </div>
        </transition-group>
    </transition>
</template>

<script>
    export default {
        name: "Errors",
        props: {
            errors: {
                type: Object,
                default: {}
            },
            wrapperClass: {
                type: String,
                default: "row pl25 pr25 pt10"
            },
            additionalWrapperClass: {
                type: String,
                default: ""
            },
            elementClass: {
                type: String,
                default: "error font_size_s mb10"
            }
        },
        computed: {
            wrapper_class() {
                return (this.wrapperClass + ' ' + this.additionalWrapperClass);
            },
            show() {
                return !(_.isEmpty(this.errors));
            }
        }
    }
</script>

<style scoped>
    .error {
        width: 100%;
        color: #721c24;
        background-color: #f8d7da;
        padding: 10px 20px;
        border: 1px solid #f5c6cb;
        border-radius: 0.25rem;
    }

    .slide-fade-enter-active {
        transition: all .3s ease;
    }
    .slide-fade-leave-active {
        transition: all .4s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    }
    .slide-fade-enter, .slide-fade-leave-to
        /* .slide-fade-leave-active до версии 2.1.8 */ {
        transform: translateX(10px);
        opacity: 0;
    }
</style>