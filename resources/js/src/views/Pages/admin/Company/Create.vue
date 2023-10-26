<template>
  <div class="app-content content">
    <div class="content-wrapper">
      <section id="user_page" class="user-page">
        <div class="page-title mb-4">
          <div class="row">
            <div class="col-12">
              <h2>Create Company</h2>
            </div>
          </div>
        </div>
        <div
          class="media-left flex-shrink-0 mb-5 mb-lg-0"
          style="width: 20%; margin-left: 40%"
        >
          <div class="profile-img text-center m-auto m-md-0">
            <div class="attached">
              <img v-if="userImage" :src="userImage" class="img-fluid ml-0" />
              <img
                src="images/avatar.png"
                alt=""
                class="img-fluid ml-0 rounded-circle"
                style="margin-left: 35%"
                v-else
              />

              <button
                name="file"
                for="picture"
                class="btn camera-btn"
                onclick="document.getElementById('picture').click()"
              >
                <i class="fa fa-camera"></i>
              </button>

              <form class="d-none">
                <input
                  type="file"
                  name="file"
                  accept=".gif,.jpg,.png,.tif|image/*"
                  @change="selected"
                  id="picture"
                />
                <input type="submit" />
              </form>
            </div>
          </div>
          <div style="margin-left: 63%">Logo</div>
        </div>
        <div class="row justify-content-between align-items-center">
          <div class="col-md-10 m-auto my-3 pt-3">
            <validation-observer v-slot="{ handleSubmit }">
              <form @submit.prevent="handleSubmit(create)">
                <div class="d-flex mb-3 flex-wrap align-items-center">
                  <div class="labelTitle col-md-3">
                    <p class="mb-0 font-weight-bold">Name:</p>
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
                        v-model="form.name"
                        placeholder="Enter Name"
                        name="Name"
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
                    <p class="mb-0 font-weight-bold">Website:</p>
                  </div>
                  <div class="fieldData col-md-9">
                    <validation-provider
                      tag="div"
                      rules="required"
                      name="Website"
                      v-slot="{ errors }"
                    >
                      <input
                        type="text"
                        v-model="form.website"
                        placeholder="Enter Website"
                        name="Website"
                        class="form-control pr-5 py-2 h-auto"
                      />

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
      userImage: "",
    };
  },
  methods: {
    ...mapActions("admin", ["store"]),
    onChange(e) {
      this.file = e.target.files[0];
    },
    async create() {
      let fd = new FormData();
      var file = document.getElementById("picture").files[0];
      if (typeof file != "undefined") {
        if (file.size > 0) {
          fd.append("logo", file);
        }
      }
      this.buildFormData(fd, this.form);
      let params = {
        route: route("admin.companies.store"),
        method: "POST",
        data: fd,
      };
      try {
        let { data } = await this.store(params);
        if (data.status) {
          this.$snotify.success(data.msg);
          this.$router.push({ name: "admin.companies.index" });
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
    async selected({ target: { files } }) {
      this.url = URL.createObjectURL(files[0]);
      this.userImage = this.url;
      console.log(this.userImage);
    },
  },
};
</script>
