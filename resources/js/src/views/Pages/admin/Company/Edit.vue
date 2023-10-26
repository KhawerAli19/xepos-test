<template>
  <div class="app-content content">
    <div class="content-wrapper">
      <section id="user_page" class="user-page">
        <div class="page-title mb-4">
          <div class="row">
            <div class="col-12">
              <h2>Edit Company Details</h2>
            </div>
          </div>
        </div>
        <div class="d-flex mb-3 flex-wrap align-items-center">
          <div class="labelTitle col-md-6">
            <p class="mb-0 font-weight-600">Name :</p>
          </div>
          <div
            class="fieldData col-md-6 d-flex flex-wrap gap-15 align-items-center"
          >
            <p class="mb-0 text-black-50">{{ detail.name }}</p>
          </div>
        </div>
        <div class="d-flex mb-3 flex-wrap align-items-center">
          <div class="labelTitle col-md-6">
            <p class="mb-0 font-weight-600">Email :</p>
          </div>
          <div
            class="fieldData col-md-6 d-flex flex-wrap gap-15 align-items-center"
          >
            <p class="mb-0 text-black-50">{{ detail.email }}</p>
          </div>
        </div>
        <div class="d-flex mb-3 flex-wrap align-items-center">
          <div class="labelTitle col-md-6">
            <p class="mb-0 font-weight-600">Website :</p>
          </div>
          <div class="fieldData col-md-6">
            <p class="mb-0 text-black-50">
              {{ detail.website }}
            </p>
          </div>
        </div>
        <div class="d-flex mb-3 flex-wrap align-items-center">
          <div class="labelTitle col-md-6">
            <p class="mb-0 font-weight-600">Logo :</p>
          </div>
          <div class="fieldData col-md-6">
            <img
              style="width: 30%"
              v-if="detail.logo"
              :src="detail.logo_url"
              class="img-fluid ml-0"
            />
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
  <script>
import { mapActions } from "vuex";
export default {
  data: () => ({
    detail: {},
  }),
  async created() {
    await this.fetch();
  },
  methods: {
    ...mapActions("admin", ["get"]),
    async fetch() {
      let params = {
        route: route("admin.companies.show", {
          id: this.$route.params.id,
        }),
      };
      let { data } = await this.get(params);
      this.detail = data.company;
    },
  },
};
</script>
