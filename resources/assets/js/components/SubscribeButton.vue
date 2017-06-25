<template>
    <div>
        <button @click="subscribe" :class="classes">Subscribe</button>
    </div>
</template>

<script>
    export default {

        props: ['active'],

        computed: {
            classes() {
                return ['btn', this.active ? 'btn-primary' : 'btn-default'];
            }
        },

        methods: {
            subscribe() {

                let requestType = this.active ? 'delete' : 'post';

                axios[requestType](location.pathname + '/subscriptions')
                     .then((data) => {
                        this.active = ! this.active;
                        flash('Your are now subscribed to this thread');
                     });
            }
        }
    }
</script>
