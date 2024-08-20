var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var HierarchyProfileModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    voterId: null,
                    proVoterId: null
                },
                errors: []
            },
            showCreateModal: false,
            header : {
                municipalityNo : "",
                municipalityName : "",
                barangayNo : "",
                barangayName : ""
            }
        };
    },

    componentDidMount: function () {
        this.loadData(this.props.proVoterId);
        console.log("load data profile");
    },


    openCreateModal : function(){
        this.setState({ showCreateModal : true });
    },

    closeCreateModal : function(){
        this.setState({ showCreateModal : false});
    },

    loadData: function (proVoterId) {
        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_household_header_by_leader_id", { id: proVoterId }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;

            form.data.proVoterId = res.pro_voter_id;
            form.data.cellphoneNo = self.isEmpty(res.cellphone) ? '' : res.cellphone;
            form.data.birthdate = !self.isEmpty(res.birthdate) ? moment(res.birthdate).format('YYYY-MM-DD') : '';
            form.data.gender = res.gender;
         
            form.data.municipalityName = res.municipality_name;
            form.data.municipalityNo = res.municipality_no;
            form.data.barangayName = res.barangay_name;
            form.data.barangayNo = res.barangay_no;
            form.data.voterName = res.voter_name;
            form.data.voterGroup = res.voter_group;
            form.data.householdId = res.household_id;

            self.setState({ form: form , householdId : res.household_id}, self.reinitSelect2);
        });
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} dialogClassName="modal-custom-85" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>{self.props.headerText} Household Profile </Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {
                        this.state.showCreateModal &&
                        <HouseholdMemberCreateModal
                            proId={3}
                            provinceCode={53}
                            municipalityNo={this.state.header.municipalityNo}
                            municipalityName={this.state.header.municipalityName}
                            barangayNo={this.state.header.barangayNo}
                            barangayName={this.state.header.barangayName}

                            electId={423}
                            householdId={this.props.id}
                            show={this.state.showCreateModal}
                            onHide={this.closeCreateModal}
                        />
                    }

                    <div className="row">
                        <div className="col-md-12">
                            <button type="button" className="btn btn-success btn-sm" style={{ marginRight: "10px" }} onClick={this.openCreateModal}>Add Household Members</button>
                        </div>
                    </div>

                    <HierarchyProfileDatatable proVoterId={this.props.proVoterId} />
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        console.log("hierarchy profile modal has been loaded");
    },

});


window.HierarchyProfileModal = HierarchyProfileModal;