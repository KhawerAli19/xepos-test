<template>
    <div class="tab-pane fade show active" id="packages" role="tabpanel" aria-labelledby="business-tab">
        <div class="titleBox py-4">
            <h3 class="mb-0 achivpFont mb-0 font-weight-bold">Smiles:</h3>
        </div>
        <div class="notification-section shadow p-5 rounded-15 my-4">
            <div class="col-md-12">
                <div class="addPackages text-md-right">
                    <router-link :to="{name : 'admin.packages.create', params : {type : 'smiles'} }" class="btn">Add Smile</router-link>
                </div>
            </div>
            <div class="row">
                <Table @page-changed="fetch" :fields="tableFields" :data="smiles">
                    <table-search 
                        show-date-ranges
                        @on-search="setCallback('search',$event,fetch)"
                        @on-change-entries="setCallback('entries',$event,fetch)"
                        @on-change-status="setCallback('currentStatus',$event,fetch)"
                        @on-change-date-from="setCallback('dateFrom',$event,fetch)"
                        @on-change-date-to="setCallback('dateTill',$event,fetch)"

                    />
                    <template slot="extra-cells" slot-scope="item">
                        <td>
                                <div class="btn-group ml-1">
                                    <button type="button" class="btn border-color outline-0" data-toggle="dropdown" aria-expanded="false"> <i class="bi bi-three-dots-vertical"></i> </button>
                                    <div class="dropdown-menu">
                                        <router-link :to="{ name : 'admin.packages.show',params : {id : item.id} }" class="dropdown-item mb-1"><i class="fa fa-eye"></i>View</router-link>
                                        <router-link :to="{ name : 'admin.packages.edit',params : {id : item.id} }" class="dropdown-item mb-1"><i class="fa fa-edit"></i>Edit</router-link>
                                        <!-- <router-link :to="{ name : 'admin.packages.purchases',params : {id : item.id} }" class="dropdown-item mb-1"><i class="bi bi-eye table-eyq pr-2 "></i>Purchases</router-link> -->
                                    
                                    </div>
                                </div>
                            </td>
                    </template>
                    </Table>
            </div>
        </div>
    </div>
</template>
<script>
import {mapActions} from 'vuex';
export default {
    computed : {
        tableFields(){
            return [{
                key : 'number_of_smiles',
                label : 'Number of Smiles',
            },{
                label : 'Amount',
                key : 'amount',
                format : (price)=> (`$${price}`),
            },{
                label : 'Created On',
                key : 'created_at',
                format : this.formatDate,
            }];
        },
    },
    data: ()=> ({
        smiles : {},
        currentStatus : '',
        search : '',
        entries : '',
        dateFrom : '',
        dateTill : '',
    }),
    async created(){
        await this.fetch();
    },
    methods : {
        ...mapActions('admin',['getAll']),
        async fetch(page = 1) {
			let params = {
				route: route('admin.packages.index',{type : 'smile'}),
				 data: {
                    page,
                    status: this.currentStatus,
                    search : this.search,
                    entries : this.entries,
                    dateFrom: this.dateFrom,
                    dateTill: this.dateTill,
                }
			};
			let { data } = await this.getAll(params);
            this.smiles = data.smile;
		},
    },
}
</script>