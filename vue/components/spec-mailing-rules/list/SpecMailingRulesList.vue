<template>
    <div class="pb50">
        <div class="b-form-row b-form-field b-field-default">
            <SpecMailingRulesSearch/>
        </div>
        <div class="b-row relative">
            <SpecMailingRulesItems/>
        </div>
    </div>
</template>

<script>
    import SpecMailingRulesSearch from "./SpecMailingRulesSearch";
    import SpecMailingRulesItems from "./SpecMailingRulesItems";

    export default {
        name: "SpecMailingRulesList",
        components: {
            SpecMailingRulesItems,
            SpecMailingRulesSearch
        }
    }
</script>

<style scoped>

</style>