<script setup>
    import { onMounted, reactive, ref } from 'vue'

    // Load any props passed from the block's view.php template here.
    const props = defineProps({
        blockId: String
    })

    const userData = reactive({
        user_name: null,
        user_id: null,
        found: false
    })

    const count = ref(0)
    const inputUserID = ref(0)
    const isLoading = ref(true)

    // OnMounted lifecycle hook to pull in initial data
    onMounted(() => {
        getData(null)
    })

    // Simple fetch call to our API controller to get data
    const getData = (uid) => {
        const userID = parseInt(uid)
        isLoading.value = true
        fetch(`/api/example?uID=${userID}`)
        .then((res) => (res.json()))
        .then((data) => {
            userData.user_name = data.name,
            userData.user_id = data.id,
            userData.found = data.found
            
        })
        .catch((error) => (console.error(error.message)))
        .finally(() => {
            isLoading.value = false
        })
    }
</script>
<template>
    <h1>Example Block</h1>
    <p class="example-block-message">Hello world from inside the example block SFC! This block's ID is {{ props.blockId }}. We are styling this paragraph inside the SFC.</p>
    <h2>The classic "count" example:</h2>
    <p>Count is {{ count }}</p>
    <button @click="count++">Increment</button>
    <button @click="count = 0">Reset</button>
    <h2>API User Data</h2>
    <!-- Could also use :class to dynamically use CSS for a loading indicator or something... -->
    <div v-if="isLoading">
        <p>Loading API data...</p>
    </div>
    <div v-else>
        <p>User Name: {{ userData.user_name }}</p>
        <p>User ID: {{ userData.user_id }}</p>
        <p>Found from Query String: {{ (userData.found) ? 'Yes, user ID matches query string' : 'No, using current logged in user' }}</p>
    </div>
    <h3>Get a user by ID:</h3>
    <form @submit.prevent="getData(inputUserID)">
        <label>Input User ID: <input v-model.number="inputUserID" /></label>
        <button type="submit">Try this User ID</button>
    </form>
</template>
<style scoped>
    .example-block-message{ font-size: 14px; line-height: 16px; padding: 20px;}
</style>