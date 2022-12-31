import { apiRequest } from "./network.js";

const { createApp } = Vue
const app = createApp({
  template: `
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-xxl-8">
            <h1>Home task - user search engine</h1>
            <div class="row justify-content-start">
              <div class="col-xxl-6 col-xl-8 d-flex">
                <select class="form-select" @change="filter($event, departments)" name="department">
                  <option value="0">All Departments</option>
                  <option v-for="department in departments" :value="department.id">{{department.name}}</option>
                </select>
                <select class="form-select" @change="filter($event, regions)" name="region">
                  <option value="0">All Regions</option>
                  <option v-for="region in regions" :value="region.id">{{region.name}}</option>
                </select>
              </div>
            </div>
            <div class="d-flex flex-row">
              <input class="rounded-0 form-control flex-grow-1" type="search" v-model="input" placeholder="Search for user (Ex: abc xyz)">
              <button class="btn btn-primary rounded-0" @click="search">Search</button>
            </div>
            <div class="alert alert-danger" v-if="error != null">
              {{error}}
            </div>
            <template v-else>
              <div class="card p-2" v-if="items.length">
                <table class="table">
                  <thead>
                    <tr>
                      <th><b>Name</b></th>
                      <th><b>Mail</b></th>
                      <th><b>Username</b></th>
                      <th><b>Region</b></th>
                      <th><b>Department</b></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in items">
                      <td v-html="highlight(item.name)"></td>
                      <td v-html="highlight(item.mail, '.', '@')"></td>
                      <td v-html="highlight(item.username, '_')"></td>
                      <td>{{item.region}}</td>
                      <td>{{item.department}}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr><td colspan="100%">Query time: {{executetime}} seconds</td></tr>
                  </tfoot>
                </table>
              </div>
              <div class="alert alert-info" v-else>
                <i class="fas fa-spin fa-spinner" v-if="searching"></i>
                All results will be rendered here
              </div>
            </template>
          </div>
        </div>
      </div>
  `,
  data() {
    return {
      error: null,
      input: "",
      items: [],
      regions: [],
      departments: [],
      filters: {},
      typing_timer: null,
      loading: false,
      searching: false,
      executetime: 0
    }
  },
  watch: {
    input(next, prev) {
      /**
       * @description This method is emitted the search method only if the input contains atleast two chars
       * @method typing
       * @param {event} e
       */
      this.items = [];
      this.error = null;
      this.searching = false;
      if (next.length < 1) return;
      this.searching = true;
      let input = this.input.replace(/\s+/g, " ");
      let inputs = input.split(" ");
      for (let search of inputs) {
        if (search.length < 2) {
          this.error = "Search field must contains atleast 2 chars in every substring";
          return;
        }
      }
      if (this.typing_timer != null) clearInterval(this.typing_timer);
      this.typing_timer = setTimeout(function() {
        this.search();
        this.typing_timer = null;
      }.bind(this), 1000);
    }
  },
  methods: {
    async search() {
      /**
       * @description fetching results from the user search
       */
      this.items = [];
      this.error = null;
      this.searching = true;
      let req = await apiRequest("user/search", {
        input: this.input,
        ...this.filters
      });
      this.searching = false;
      if (req.status) {
        this.items = req.data;
        this.executetime = req.time;
      }
    },
    highlight(text, delimeter = " ", substring = null) {
      /**
       * @description highlight text by search params
       * @param {string} text
       * @return {string}
       */
      let toHightlight = text;
      if (substring != null)
        toHightlight = text.substring(0, text.indexOf(substring));
      let leftOvers = text.substring(toHightlight.length);
      for (let input of this.input.split(delimeter)) {
        let reg = new RegExp("^"+input+"|"+delimeter+input, "gi");
        toHightlight = toHightlight.replace(reg, function(str) {return '<b>'+str+'</b>'});
      }
      return toHightlight+leftOvers;
    },
    filter(e, options) {
      /**
       * @description adding region and department filter to search
       * @param {event} e 
       * @param {array} options 
       */
      let name = e.target.getAttribute("name");
      let index = options.findIndex((item) => {
        return item.id == e.target.value;
      })
      if (index < 0) delete this.filters[name];
      else this.filters[name] = e.target.value;
      if (this.input.length > 0)
        this.search();
    }
  },
  async created() {
    [this.departments, this.regions] = await Promise.all([
      apiRequest("user/departments"),
      apiRequest("user/regions")
    ]);
  }
}).mount('#app')