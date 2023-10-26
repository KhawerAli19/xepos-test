    <template>
  <div class="app-content content">
    <div class="content-wrapper">
      <div class="content-body">
        <!-- Basic form layout section start -->
        <section id="configuration" class="dashboard">
          <div class="row">
            <div class="col-12">
              <div class="card bg-transparent shadow-none mb-0">
                <div class="card-body p-md-2 p-lg-2">
                  <div class="page-title mb-0">
                    <div class="row">
                      <div class="col-12 col-sm-12 mb-3">
                        <h2>Dashboard</h2>
                      </div>
                    </div>
                  </div>

                  <div class="card-dashboard mt-2 mt-md-3 mb-0 mb-md-3">
                    <div class="row">
                      <div
                        class="col-12 col-md-6 col-lg-6 col-xl-4 mb-2 d-flex"
                      >
                        <div class="card">
                          <div class="card-title pt-4">Total Companies</div>
                          <div class="card-body py-1">
                            <div class="media align-items-center d-flex w-100">
                              <div class="media-body text-left flex-grow-1">
                                <h3>{{ detail.total_companies }}</h3>
                              </div>
                              <div class="align-self-center flex-shrink-1">
                                <img src="images/icon-user.png" alt="" />
                              </div>
                            </div>
                          </div>
                          <div
                            class="card-footer bg-transparent border-0 pt-0 pb-4"
                          >
                            <h4>
                              100% <i class="fas fa-arrow-up"></i> Since Last
                              Week
                            </h4>
                          </div>
                        </div>
                      </div>
                      <div
                        class="col-12 col-md-6 col-lg-6 col-xl-4 mb-2 d-flex align-items-stretch"
                      >
                        <div class="card">
                          <div class="card-title pt-4">Total Employees</div>
                          <div class="card-body py-1">
                            <div class="media align-items-center d-flex w-100">
                              <div class="media-body text-left flex-grow-1">
                                <h3>{{ detail.total_employees }}</h3>
                              </div>
                              <div class="align-self-center flex-shrink-1">
                                <img src="images/icon-medication.png" alt="" />
                              </div>
                            </div>
                          </div>
                          <div
                            class="card-footer bg-transparent border-0 pt-0 pb-4"
                          >
                            <h4>
                              10% <i class="fas fa-arrow-up"></i> Since Last
                              Week
                            </h4>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script src="admins/app-assets/vendors/js/charts/chart.min.js" type="text/javascript"></script>
 <script src="admins/app-assets/vendors/js/charts/echarts/echarts.js" type="text/javascript"></script>
 <script src="admins/app-assets/js/scripts/charts/echarts/bar-column/basic-column.js" type="text/javascript"></script>
 <script src="admins/app-assets/js/scripts/charts/echarts/pie-doughnut/basic-pie.js" type="text/javascript"></script>
<script>
import { mapActions, mapState } from "vuex";
const DashboardCards = () => import("./components/DashboardCards.vue");

export default {
  components: {
    DashboardCards,
  },
  data() {
    return {
      detail: "",
    };
  },
  computed: {
    ...mapState("admin", ["home"]),
  },
  async created() {
    let self = this;
    await this.fetch().then((res) => {
      self.loaded = true;
    });
  },

  methods: {
    ...mapActions("admin", ["getAll"]),

    async fetch() {
      this.loaded = false;
      let params = {
        route: route("admin.home"),
      };
      let { data } = await this.getAll(params);
      this.detail = data;
    },
  },
};
</script>
