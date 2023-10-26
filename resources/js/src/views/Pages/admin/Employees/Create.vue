<template>
  <div class="app-content content">
    <div class="content-wrapper">
      <section id="user_page" class="user-page">
        <div class="page-title mb-4">
          <div class="row">
            <div class="col-12">
              <h2>Create Employee</h2>
            </div>
          </div>
        </div>
        <div class="row justify-content-between align-items-center">
          <div class="col-md-10 m-auto my-3 pt-3">
            <validation-observer v-slot="{ handleSubmit }">
              <form @submit.prevent="handleSubmit(create)">
                <div class="d-flex mb-3 flex-wrap align-items-center">
                  <div class="labelTitle col-md-3">
                    <p class="mb-0 font-weight-bold">First Name:</p>
                  </div>
                  <div class="fieldData col-md-9">
                    <validation-provider
                      tag="div"
                      rules="required"
                      name="First name"
                      v-slot="{ errors }"
                    >
                      <input
                        type="text"
                        v-model="form.first_name"
                        placeholder="Enter First Name"
                        name="Name"
                        class="form-control pr-5 py-2 h-auto"
                      />
                      <div class="text-danger">{{ errors[0] }}</div>
                    </validation-provider>
                  </div>
                </div>
                <div class="d-flex mb-3 flex-wrap align-items-center">
                  <div class="labelTitle col-md-3">
                    <p class="mb-0 font-weight-bold">Last Name:</p>
                  </div>
                  <div class="fieldData col-md-9">
                    <validation-provider
                      tag="div"
                      rules="required"
                      name="Last Name"
                      v-slot="{ errors }"
                    >
                      <input
                        type="text"
                        v-model="form.last_name"
                        placeholder="Enter Last Name"
                        name="Last Name"
                        class="form-control pr-5 py-2 h-auto"
                      />
                      <div class="text-danger">{{ errors[0] }}</div>
                    </validation-provider>
                  </div>
                </div>
                <div class="d-flex mb-3 flex-wrap align-items-center">
                  <div class="labelTitle col-md-3">
                    <p class="mb-0 font-weight-bold">Email:</p>
                  </div>
                  <div class="fieldData col-md-9">
                    <validation-provider
                      tag="div"
                      rules="required|email"
                      name="email"
                      v-slot="{ errors }"
                    >
                      <input
                        type="text"
                        v-model="form.email"
                        placeholder="Enter Email"
                        name="email"
                        class="form-control pr-5 py-2 h-auto"
                      />
                      <div class="text-danger">{{ errors[0] }}</div>
                    </validation-provider>
                  </div>
                </div>
                <div class="d-flex mb-3 flex-wrap align-items-center">
                  <div class="labelTitle col-md-3">
                    <p class="mb-0 font-weight-bold">Phone:</p>
                  </div>
                  <div class="fieldData col-md-9">
                    <validation-provider
                      tag="div"
                      rules="required"
                      name="Phone"
                      v-slot="{ errors }"
                    >
                      <input
                        type="text"
                        v-model="form.phone"
                        placeholder="Enter Phone No"
                        name="Phone No"
                        class="form-control pr-5 py-2 h-auto"
                      />
                      <div class="text-danger">{{ errors[0] }}</div>
                    </validation-provider>
                  </div>
                </div>
                <div class="d-flex mb-3 flex-wrap align-items-center">
                  <div class="labelTitle col-md-3">
                    <p class="mb-0 font-weight-bold">Company:</p>
                  </div>
                  <div class="fieldData col-md-9">
                    <validation-provider
                      tag="div"
                      rules="required"
                      name="Company"
                      v-slot="{ errors }"
                    >
                      <!-- <select
                        v-model="form.company"
                        name=""
                        class="form-control primary"
                        style="background-color: white"
                        id=""
                      >
                        <option value="">Medicine Type</option>
                      </select> -->

                      <v-select
                        style="border: none"
                        :options="companies"
                        id=""
                        v-model="form.company"
                        label="name"
                        class="form-control primary"
                      ></v-select>

                      <div class="text-danger">{{ errors[0] }}</div>
                    </validation-provider>
                  </div>
                </div>

                <div class="submitBtn text-center pt-5">
                  <input
                    type="submit"
                    value="ADD"
                    class="btn btn-icon btn-secondary mr-1"
                  />
                </div>
              </form>
            </validation-observer>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>


<script>
import { mapActions, mapState } from "vuex";
export default {
  data() {
    return {
      form: {},
      companies: [],
    };
  },
  created() {
    this.fetch();
  },
  methods: {
    ...mapActions("admin", ["store", "getAll"]),
    async fetch() {
      let params = {
        route: route("admin.companies.index"),
      };
      let { data } = await this.getAll(params);
      this.companies = data.company.data;
    },
    async create() {
      let fd = new FormData();
      this.buildFormData(fd, this.form);
      let params = {
        route: route("admin.employees.store"),
        method: "POST",
        data: fd,
      };
      try {
        let { data } = await this.store(params);
        if (data.status) {
          this.$snotify.success(data.msg);
          this.$router.push({ name: "admin.employees.index" });
          this.form = {
            permissions: [],
          };
        } else {
          this.$snotify.error("something went wrong");
        }
      } catch (e) {
        if (e.response) {
          this.$refs.adminObserver.setErrors(e.response.data.errors);
        }
        console.log(e);
      }
    },
  },
};
</script>
