<template>
    <div>
        <div class="font_size_xs font_caps font_bold">
            <span>ИСКЛЮЧЕНИЯ ТИПОВ ДОСТАВКИ</span>
        </div>
        <ul class="b-delivery-exceptions">
            <li class="b-delivery-exception-item pointer"
                 v-for="(delivery, index) in delivery_list"
                 :key="index"
                 @click="toggleDelivery(delivery.type, delivery.kind)"
            >
                <div class="b-form-checkbox font_size_s"
                     :class="{active: isSelectedDelivery(delivery.type, delivery.kind)}">
                    <span class="b-form-checkbox-icon"></span>
                    <span class="font_size_s">{{ delivery.title }}</span>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from "vuex";
    export default {
        name: "DeliveryExceptions",
        data() {
            return {
                delivery_list: [
                    {
                        title: 'Самовывоз Лабиринт',
                        type: 1,
                        kind: null,
                    },
                    {
                        title: 'Курьерка',
                        type: 4,
                        kind: null,
                    },
                    {
                        title: 'Почта РФ',
                        type: 2,
                        kind: null,
                    },
                    {
                        title: 'ПР Зарубежье',
                        type: 3,
                        kind: null,
                    },
                    {
                        title: 'Dimex',
                        type: 8,
                        kind: null,
                    },
                    {
                        title: 'DHL-Зарубеж',
                        type: 6,
                        kind: 47,
                    },
                    {
                        title: 'Boxberry',
                        type: 11,
                        kind: null,
                    },
                    {
                        title: 'X5',
                        type: 12,
                        kind: null,
                    },
                    {
                        title: 'DHL-Россия',
                        type: 6,
                        kind: 26,
                    }
                ]
            }
        },
        computed: {
            ...mapGetters('spec_mailing_rule', {
                delivery_exceptions: 'GET_DELIVERY_EXCEPTIONS'
            })
        },
        methods: {
            ...mapActions('spec_mailing_rule', {
                changeDeliveryExceptions: 'CHANGE_DELIVERY_EXCEPTIONS'
            }),
            toggleDelivery: function(type, kind) {
                let delivery = {delivery_kind_type: type, delivery_kind: kind};
                let delivery_exceptions = this.delivery_exceptions.slice();
                let index = _.findIndex(this.delivery_exceptions, delivery);
                if (index !== -1) {
                    delivery_exceptions.splice(index, 1);
                } else {
                    delivery_exceptions.push(delivery)
                }
                this.changeDeliveryExceptions(delivery_exceptions);
            },
            isSelectedDelivery: function (type, kind) {
                let delivery = {delivery_kind_type: type, delivery_kind: kind};
                return _.find(this.delivery_exceptions, delivery);
            }
        }
    }
</script>

<style scoped>
    .b-delivery-exception-item {
        padding-top: 10px;
    }
</style>