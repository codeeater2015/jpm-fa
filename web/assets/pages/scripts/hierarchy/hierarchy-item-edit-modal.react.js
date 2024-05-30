var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var HierarchyItemEditModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    voterId: null,
                    proVoterId: null
                },
                errors: []
            }
        };
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} bsSize="sm" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Update Profile</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="voter-recruit-create-form" onSubmit={this.submit}>
                        <div className="row">

                            <div className="col-md-12">
                                <FormGroup controlId="formContactNo" validationState={this.getValidationState('contactNo')}>
                                    <ControlLabel > Cellphone No : </ControlLabel>
                                    <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.contactNo} className="input-sm form-control" onChange={this.setFormProp} name="contactNo" />
                                    <HelpBlock>{this.getError('contactNo')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-12" style={{ paddingRight: "0" }}>
                                <FormGroup controlId="formVoterGroup" validationState={this.getValidationState('voterGroup')}>
                                    <ControlLabel >Position : </ControlLabel>
                                    <select id="edit-voter-group-select2" className="form-control input-sm">
                                        <option value=""> </option>
                                    </select>
                                    <HelpBlock>{this.getError('voterGroup')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-12 text-right">
                                <button className="btn btn-default" type="button" onClick={this.props.onHide} > Close </button>
                                <button className="btn btn-primary" type="submit" style={{ marginLeft: "10px" }}> Submit </button>
                            </div>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.initSelect2();
        this.loadData(this.props.proVoterId);
    },

    loadData: function (proVoterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_hierarchy_item", { proVoterId: proVoterId }),
            type: "GET"
        }).done(function (res) {
            console.log('update profile data has been received');
            console.log(res);
            var form = self.state.form;

            form.data = res;
            self.setState({ form: form }, self.reinitSelect2);
        });
    },

    initSelect2: function () {
        var self = this;

        $("#edit-voter-group-select2").select2({
            casesentitive: false,
            placeholder: "Enter Group",
            width: '100%',
            allowClear: true,
            tags: true,
            containerCssClass: ":all:",
            createTag: function (params) {
                return {
                    id: params.term.toUpperCase(),
                    text: params.term.toUpperCase(),
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_hierarchy_select2_voter_group'),
                data: function (params) {
                    return {
                        searchText: params.term, // search term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.voter_group, text: item.voter_group };
                        })
                    };
                },
            }
        });

        $("#edit-voter-group-select2").on("change", function () {
            self.setFormPropValue("voterGroup", $(this).val());
        });
    },

    reinitSelect2: function () {
        var voterGroup = this.state.form.data.voterGroup;
        $("#edit-voter-group-select2").empty()
            .append($("<option />")
                .val(voterGroup)
                .text(voterGroup))
            .trigger("change");
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

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.proId = self.props.proId;
        data.voterGroup = $('#edit-voter-group-select2').val();

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_hierarchy_patch_item_info", { proVoterId: self.props.proVoterId }),
            data: data,
            type: 'PATCH'
        }).done(function (res) {
            console.log('patch complete');
            self.props.onSuccess();
            self.props.onHide();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    }
});


window.HierarchyItemEditModal = HierarchyItemEditModal;