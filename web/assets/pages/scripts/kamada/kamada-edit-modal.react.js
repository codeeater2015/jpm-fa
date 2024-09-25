var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var KamadaEditModal = React.createClass({

    getInitialState : function(){
        return {
            form : {
                data : {
                    eventName : "",
                    eventDate : null,
                    status : ""
                },
                errors : []
            }
        };
    },

    render : function(){
        var self = this;

        return (
            <Modal style={{ marginTop : "10px" }} keyboard={false} bsSize="lg" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Edit Kamada Leader</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                <form id="kamada-edit-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-3">

                                <FormGroup controlId="formBarangay" validationState={this.getValidationState('barangayNo')}>
                                    <label className="control-label">City/Municipality</label>
                                    <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                    </select>
                                    <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-3">
                                <FormGroup controlId="formBarangay" validationState={this.getValidationState('barangayNo')}>
                                    <label className="control-label">Barangay</label>
                                    <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                                    </select>
                                    <HelpBlock>{this.getError('barangayNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-3">
                                <FormGroup controlId="formPurok" validationState={this.getValidationState('purok')}>
                                    <label className="control-label">PUROK / SITIO</label>
                                    <select id="purok_select2" className="form-control form-filter input-sm" name="purok">
                                    </select>
                                    <HelpBlock>{this.getError('purok')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <br/>
                            <div className="col-md-5">
                                <FormGroup controlId="formTlProVoterId" validationState={this.getValidationState('tlProVoterId')}>
                                    <ControlLabel > Top Leader Name : </ControlLabel>
                                    <select id="top-leader-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('tlProVoterId')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-6">
                                <FormGroup controlId="formProVoterId" validationState={this.getValidationState('proVoterId')}>
                                    <ControlLabel > Leader Name : </ControlLabel>
                                    <select id="leader-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('proVoterId')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-2">
                                <button style={{ marginTop: "26px" }} onClick={this.openNewVoterCreateModal} className="btn btn-primary btn-sm" type="button"> Add Non-Voter </button>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-3" >
                                <FormGroup controlId="formCellphoneNo" validationState={this.getValidationState('cellphone')}>
                                    <ControlLabel > Cellphone No : </ControlLabel>
                                    <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.cellphoneNo} className="input-sm form-control" onChange={this.setFormProp} name="cellphoneNo" />
                                    <HelpBlock>{this.getError('cellphone')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-3">
                                <div className="form-group">
                                    <label className="control-label">Position</label>
                                    <select id="voter_group_select2" className="form-control form-filter input-sm" name="voterGroup">
                                    </select>
                                    <HelpBlock>{this.getError('voterGroup')}</HelpBlock>
                                </div>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-12 text-right">
                                <button className="btn btn-primary btn-sm" style={{ marginRight: "10px" }} type="submit"> Submit </button>
                                <button className="btn btn-default btn-sm" type="button" onClick={this.props.onHide} > Close </button>
                            </div>
                        </div>

                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount : function(){
        this.initSelect2();
        this.loadData(this.props.id);
    },

    loadData : function(id){
        var self = this;

        self.requestEvent = $.ajax({
            url : Routing.generate("ajax_get_kamada_header", { id : id }),
            type : "GET"
        }).done(function(res){
            var form  = self.state.form;
            form.data = res;

            self.setState({ form : form }, self.reinitSelect2);
        });
    },

    reinitSelect2: function () {
        var data = this.state.form.data;

        $("#kamada-edit-form #municipality_select2").empty()
            .append($("<option/>")
                .val(data.municipalityNo)
                .text(data.municipalityName))
            .trigger("change");

        $("#kamada-edit-form #barangay_select2").empty()
            .append($("<option/>")
                .val(data.barangayNo)
                .text(data.barangayName))
            .trigger("change");

        $("#kamada-edit-form #purok_select2").empty()
            .append($("<option/>")
                .val(data.assignedPurok)
                .text(data.assignedPurok))
            .trigger("change");


        $("#kamada-edit-form #leader-select2").empty()
            .append($("<option/>")
                .val(data.proVoterId)
                .text(data.voterName))
            .trigger("change");


        $("#kamada-edit-form #top-leader-select2").empty()
            .append($("<option/>")
                .val(data.tlProVoterId)
                .text(data.tlVoterName))
            .trigger("change");

        $("#kamada-edit-form #voter_group_select2").empty()
            .append($("<option/>")
                .val(data.voterGroup)
                .text(data.voterGroup))
            .trigger("change");
    },

    initSelect2: function () {
        var self = this;

        $("#kamada-edit-form #municipality_select2").select2({
            casesentitive: false,
            placeholder: "Select City/Municipality",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: self.state.provinceCode
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.municipality_no, text: item.name };
                        })
                    };
                },
            }
        });

        $("#kamada-edit-form #barangay_select2").select2({
            casesentitive: false,
            placeholder: "Select Barangay",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_barangay'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        municipalityNo: $("#kamada-edit-form #municipality_select2").val(),
                        provinceCode: self.state.provinceCode
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.brgy_no, text: item.name };
                        })
                    };
                },
            }
        });

        $("#kamada-edit-form #leader-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_select2_project_voters'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        electId : 423,
                        provinceCode : 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var voterStatus = parseInt(item.is_non_voter) == 0 ? "V" : "NV";
                            var position = (item.position == null || item.position == '') ? "No Household" : item.position;
                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' ) - ' + voterStatus + '|' + position;

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });


        $("#kamada-edit-form #top-leader-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_select2_project_voters'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        electId : 423,
                        provinceCode : 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var voterStatus = parseInt(item.is_non_voter) == 0 ? "V" : "NV";
                            var position = (item.position == null || item.position == '') ? "No Household" : item.position;
                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' ) - ' + voterStatus + '|' + position;

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });

        $("#kamada-edit-form #voter_group_select2").select2({
            casesentitive: false,
            placeholder: "Select Position",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_voter_group'),
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.voter_group, text: item.voter_group };
                        })
                    };
                },
            }
        });


        $("#kamada-edit-form #purok_select2").select2({
            casesentitive: false,
            placeholder: "Enter Group",
            width: '100%',
            allowClear: true,
            tags: true,
            containerCssClass: ":all:",
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_kamada_select2_purok'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.assigned_purok, text: item.assigned_purok };
                        })
                    };
                },
            }
        });

        $("#kamada-edit-form #municipality_select2").on("change", function () {
            self.setFormPropValue('municipalityNo', $(this).val());
        });

        $("#kamada-edit-form #barangay_select2").on("change", function () {
            self.setFormPropValue('barangayNo', $(this).val());
        });

        $("#kamada-edit-form #purok_select2").on("change", function () {
            self.setFormPropValue('assignedPurok', $(this).val());
        });

        $("#kamada-edit-form #leader-select2").on("change", function () {
            self.loadVoter(self.props.proId, $(this).val());
        });

        $("#kamada-edit-form #top-leader-select2").on("change", function () {
            self.setFormPropValue("tlProVoterId",$(this).val());
        });
    },

    loadVoter: function (proId, proVoterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, proVoterId: proVoterId }),
            type: "GET"
        }).done(function (res) {


            var form = self.state.form;
            form.data.proVoterId = res.proVoterId;
            form.data.voterName = res.voterName;
            form.data.cellphone = res.cellphone;
            form.data.voterGroup = res.voterGroup;

            $("#kamada-edit-form #voter_group_select2").empty()
                .append($("<option/>")
                    .val(res.voterGroup)
                    .text(res.voterGroup))
                .trigger("change");


            self.setState({ form: form });
        });

        var form = self.state.form;

        form.data.proVoterId = null;
        form.data.cellphone = '';
        form.data.voterName = '';
        form.data.voterGroup = '';

        self.setState({ form: form })
    },


    setFormPropValue: function (field, value) {
        var form = this.state.form;
        form.data[field] = value;
        this.setState({ form: form });
    },

    setFormProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.value;
        this.setState({ form: form });
    },

    setFormCheckProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.checked ? 1 : 0;
        this.setState({ form: form })
    },

    setErrors: function (errors) {
        var form = this.state.form;
        form.errors = errors;
        this.setState({ form: form });
    },

    getError: function (field) {
        var errors = this.state.form.errors;
        for (var errorField in errors) {
            if (errorField == field)
                return errors[field];
        }
        return null;
    },

    getValidationState: function (field) {
        return this.getError(field) != null ? 'error' : '';
    },

    isEmpty: function (value) {
        return value == null || value == '';
    },

    reset: function () {
        var form = this.state.form;
        form.errors = [];

        this.setState({ form: form });
    },

    closeNewVoterCreateModal: function () {
        this.setState({ showNewVoterCreateModal: false });
    },

    openNewVoterCreateModal: function () {
        console.log('opening modal');
        this.setState({ showNewVoterCreateModal: true })
    },

    notify: function (message, color) {
        $.notific8('zindex', 11500);
        $.notific8(message, {
            heading: 'System Message',
            color: color,
            life: 5000,
            verticalEdge: 'right',
            horizontalEdge: 'top',
        });
    },

    submit : function(e){
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.proId = self.props.proId;
        
        self.requestPost = $.ajax({
            url: Routing.generate("ajax_patch_kamada_header",{ id : self.props.id }),
            data: data,
            type: 'PATCH'
        }).done(function(res){
            self.reset();
            self.props.reload();
            self.props.onHide();
        }).fail(function(err){
             self.setErrors(err.responseJSON);
        });
    }
});


window.KamadaEditModal = KamadaEditModal;