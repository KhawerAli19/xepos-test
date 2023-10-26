<template>
  <div class="app-content content">
    <div class="content-wrapper">
      <section id="user_page" class="user-page">
        <div class="page-title mb-4">
          <div class="row">
            <div class="col-12">
              <h2>Companies</h2>
            </div>
          </div>
        </div>
        <div class="content-body bg-white rounded-10 shadow-sm p-4 p-lg-5">
          <div class="addPackages text-md-right">
            <button type="button" class="btn btn-icon btn-secondary mr-1">
              <router-link :to="{ name: 'admin.companies.create' }"
                >Add Company</router-link
              >
            </button>
          </div>
          <div class="dataTables_wrapper">
            <Table @page-changed="fetch" :fields="tableFields" :data="company">
              <table-search
                show-status
                @on-search="setCallback('search', $event, fetch)"
                @on-change-entries="setCallback('entries', $event, fetch)"
              />
              <template #extra-heads>
                <th>Action</th>
              </template>
              <template #extra-cells="item">
                <td>
                  <div class="btn-group ml-1">
                    <button
                      type="button"
                      class="btn dropdown-toggle btn-sm"
                      data-bs-toggle="dropdown"
                    >
                      <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu">
                      <router-link
                        :to="{
                          name: 'admin.companies.show',
                          params: { id: item.id },
                        }"
                        class="dropdown-item"
                        ><i class="fa fa-eye"></i>View</router-link
                      >
                      <router-link
                        :to="{
                          name: 'admin.companies.edit',
                          params: { id: item.id },
                        }"
                        class="dropdown-item"
                        ><i class="fa fa-edit"></i>Edit</router-link
                      >
                    </div>
                  </div>
                </td>
              </template>
            </Table>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import { mapActions, mapState } from "vuex";
export default {
  computed: {
    tableFields() {
      return [
        {
          label: "Name",
          key: "name",
        },
        {
          label: "Email",
          key: "email",
        },
        {
          label: "Logo",
          key: "logo",
        },
        {
          label: "Website",
          key: "website",
        },
      ];
    },
  },
  components: {},
  data() {
    return {
      company: {},
      entries: 10,
      search: null,
    };
  },
  created() {
    let { page } = this.$route.query;
    this.fetch(page);
  },
  watch: {
    search: function (val, oldVal) {
      this.fetch();
    },
    entries: function (val, oldVal) {
      this.fetch();
    },
  },
  methods: {
    ...mapActions("admin", ["getAll"]),
    async fetch(page = 1) {
      let params = {
        route: route("admin.companies.index"),
        data: {
          page,
          search: this.search,
          entries: this.entries,
        },
      };
      let { data } = await this.getAll(params);
      this.company = data.company;
    },
  },
};
</script>
