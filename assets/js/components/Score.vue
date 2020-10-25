<template>
  <div id="score">
    <div v-if="route.score == 0">
      <div class="alert alert-danger" role="alert">
        No score yet!
      </div>
    </div>
    <div v-else>
      {{ route.score }} {{ message }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'Score',
  props: ['route'],
  data() {
    return {
      message: 'Rating'
    }
  },
  created() {
    //todo Change absolute paths
    const u = new URL('http://localhost:3000/.well-known/mercure');
    u.searchParams.append('topic', 'http://localhost:10000/api/routes/' + this.route.id);

    const es = new EventSource(u);
    es.onmessage = e => {
      const route = JSON.parse(e.data);
      this.route.score = route.score;
    }
  }
}
</script>

<style scoped>

</style>