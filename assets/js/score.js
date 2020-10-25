import Vue from 'vue';
import Score from "./components/Score";

var scoreVm = new Vue({
    el: '#score',
    data: {
        route: {
            score: 0,
            id: 0
        }
    },
    template: '<Score v-bind:route="route" />',
    components: { Score },
    beforeMount() {
        this.route.score = this.$el.attributes['data-route-score'].value
        this.route.id = this.$el.attributes['data-route-id'].value
    }
});