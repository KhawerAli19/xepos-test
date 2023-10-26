<template>
  <div class="user-listing-top">
    <div class="row align-items-end d-flex mb-3">
      <div class="col-12 col-lg-12 col-xxl-8 mb-2 mb-md-0"></div>

      <div
        class="col-12 col-md-6 col-xxl-12 mt-3 d-xl-flex d-block justify-content-start justify-content-lg-end align-items-center order-md-2"
      >
        <div class="user-record-lenght">
          <div class="select-wrapper d-block d-inline-md-block">
            <select v-model="entries" class="form-control d-inline-block">
              <option value="10">Records Per Page</option>
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
        </div>
      </div>
      <div
        class="col-12 col-md-6 col-xxl-4 text-start mt-3 mt-xxl-0 order-md-1"
      >
        <div
          class="dataTables_filter d-flex justify-content-start flex-shrink-1"
        >
          <label
            for=""
            class="d-none d-md-inline-block me-2 me-lg-3 my-0 align-self-center flex-shrink-0 fw-light"
            >Search</label
          >
          <div class="search-wrap flex-grow-1">
            <input
              v-model="searchValue"
              type="search"
              class="form-control"
              placeholder="Search"
            />
          </div>
        </div>
      </div>
    </div>
    <div class="row"></div>
  </div>
</template>
<script>
import { mapState, mapActions, mapMutations } from "vuex";
export default {
  props: {
    placeholder: {
      type: String, // String, Number, Boolean, Function, Object, Array
      required: false,
      default: "Search...",
    },
    showEntries: {
      type: Boolean, // String, Number, Boolean, Function, Object, Array
      required: false,
      default: true,
    },
    showDateRanges: {
      type: Boolean, // String, Number, Boolean, Function, Object, Array
      required: false,
      default: false,
    },
  },
  data() {
    return {
      searchValue: "",
      entries: 10,
    };
  },
  watch: {
    searchValue: function (val, oldVal) {
      this.handleSearch();
    },
    entries(val) {
      this.$emit("on-change-entries", val);
    },
  },
  created() {
    this.handleSearch = _.debounce(this.handleSearch, 500);
  },

  methods: {
    ...mapMutations("admin", ["SET_SEARCH", "SET_ENTRIES"]),
    handleSearch() {
      this.$emit("on-search", this.searchValue);
    },
  },
};
</script>
