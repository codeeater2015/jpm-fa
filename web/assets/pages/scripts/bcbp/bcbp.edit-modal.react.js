var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var BcbpEditModal = React.createClass({

    getInitialState: function () {
        return {
            showNewProfileModal: false,
            form: {
                data: {
                    municipalityNo: "",
                    barangayNo: "",
                    cellphoneNo: ""
                },
                errors: []
            }
        };
    },

    loadProfile: function (id) {

        console.log("loading profile");
        console.log(id);

        var self = this;

        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_bcbp_profile", { id: id }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;
            form.data.contactNumber = res.contactNumber;
            form.data.firstname = res.firstname;
            form.data.lastname = res.lastname;
            form.data.birthdate = res.birthdate;
            form.data.chapterName = res.chapterName;
            form.data.batchName = res.batchName;
            form.data.nickname = res.nickname;
            form.data.unitName = res.unitName;
            form.data.groupName = res.groupName;
            form.data.position = res.position;
            
            $("#chapter_select2").empty()
            .append($("<option />")
                .val(res.chapterName)
                .text(res.chapterName))
            .trigger("change");

            $("#batch_select2").empty()
            .append($("<option />")
                .val(res.batchName)
                .text(res.batchName))
            .trigger("change");
            
            $("#gender_select2").empty()
            .append($("<option />")
                .val(res.gender)
                .text(res.gender))
            .trigger("change");

            $("#position_select2").empty()
            .append($("<option />")
                .val(res.position)
                .text(res.positionr))
            .trigger("change");

            $("#group_select2").empty()
            .append($("<option />")
                .val(res.groupName)
                .text(res.groupName))
            .trigger("change");
            
            $("#unit_select2").empty()
            .append($("<option />")
                .val(res.unitName)
                .text(res.unitName))
            .trigger("change");

            self.setState({ form: form });
        });
    },

    render: function () {
        var self = this;
        var data = this.state.form.data;

        return (
            <Modal style={{ marginTop: "10px" }} bsSize="lg" keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Edit BCBP Profile</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {
                        this.state.showNewProfileModal &&
                        <TupadCreateNewProfileModal
                            proId={3}
                            electId={4}
                            provinceCode={53}
                            show={this.state.showNewProfileModal}
                            onHide={this.closeNewProfileModal}
                            municipalityNo={this.props.municipalityNo}
                            municipalityName={this.props.municipalityName}
                            barangayNo={this.props.barangayNo}
                            barangayName={this.props.barangayName}
                        />
                    }

                    <form id="fa-form" >
                        <div className="row">
                            <div className="col-md-12">

                                <div className="row">
                                    <div className="col-md-3">
                                        <FormGroup controlId="formChapterName" validationState={this.getValidationState('chapterName')}>
                                            <ControlLabel> Chapter : </ControlLabel>
                                            <select id="chapter_select2" className="form-control form-filter input-md" name="chapterName">
                                            </select>
                                            <HelpBlock>{this.getError('chapterName')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-3">
                                        <FormGroup controlId="formBatchName" validationState={this.getValidationState('batchName')}>
                                            <ControlLabel> Batch : </ControlLabel>
                                            <select id="batch_select2" className="form-control form-filter input-md" name="batchName">
                                            </select>
                                            <HelpBlock>{this.getError('batchName')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-3">
                                        <FormGroup controlId="formPosition" validationState={this.getValidationState('position')}>
                                            <ControlLabel> Position : </ControlLabel>
                                            <select id="position_select2" className="form-control form-filter input-md" name="position">
                                            </select>
                                            <HelpBlock>{this.getError('position')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-md-3">
                                        <FormGroup controlId="formUnitName" validationState={this.getValidationState('unitName')}>
                                            <ControlLabel> Unit : </ControlLabel>
                                            <select id="unit_select2" className="form-control form-filter input-md" name="unitName">
                                            </select>
                                            <HelpBlock>{this.getError('unitName')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-3">
                                        <FormGroup controlId="formGroupName" validationState={this.getValidationState('groupName')}>
                                            <ControlLabel> Action Group : </ControlLabel>
                                            <select id="group_select2" className="form-control form-filter input-md" name="groupName">
                                            </select>
                                            <HelpBlock>{this.getError('groupName')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-md-3" >
                                        <FormGroup controlId="formFirstname" validationState={this.getValidationState('firstname')}>
                                            <ControlLabel > Firstname : </ControlLabel>
                                            <input type="text" value={this.state.form.data.firstname} name="firstname" className="input-md form-control" onChange={this.setFormProp} />
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-3" >
                                        <FormGroup controlId="formLastname" validationState={this.getValidationState('lastname')}>
                                            <ControlLabel > Lastname : </ControlLabel>
                                            <input type="text" value={this.state.form.data.lastname} name="lastname" className="input-md form-control" onChange={this.setFormProp} />
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-3" >
                                        <FormGroup controlId="formBirthdate" validationState={this.getValidationState('birthdate')}>
                                            <ControlLabel > Birthday : </ControlLabel>
                                            <input type="date" value={this.state.form.data.birthdate} className="input-md form-control" name="birthdate" onChange={this.setFormProp} />
                                        </FormGroup>
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-md-3" >
                                        <FormGroup controlId="formNickname" validationState={this.getValidationState('nickname')}>
                                            <ControlLabel > Nickname : </ControlLabel>
                                            <input type="text"  value={this.state.form.data.nickname}  name="nickname" className="input-md form-control" onChange={this.setFormProp} />
                                        </FormGroup>
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-md-3">
                                        <FormGroup controlId="formGender" validationState={this.getValidationState('gender')}>
                                            <ControlLabel> Gender : </ControlLabel>
                                            <select id="gender_select2" className="form-control form-filter input-md" name="gender">
                                            </select>
                                            <HelpBlock>{this.getError('gender')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-3" >
                                        <FormGroup controlId="formContactNo" validationState={this.getValidationState('contactNumber')}>
                                            <ControlLabel > Cellphone No: </ControlLabel>
                                            <input type="text" value={this.state.form.data.contactNumber} name="contactNumber" className="input-md form-control" onChange={this.setFormProp} />
                                            <HelpBlock>{this.getError('contactNumber')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="clearfix" />

                        <div className="text-right" style={{ marginTop: "30px" }}>
                            <button type="button" className="btn-lg btn-default" style={{ marginRight: "5px" }} onClick={this.props.onHide}>Close</button>
                            <button type="button" className="btn-lg btn-primary" onClick={this.submit}>Submit</button>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.initSelect2();
        this.loadProfile(this.props.id);
    },


    initSelect2: function () {
        var self = this;

        $("#fa-form #chapter_select2").select2({
            casesentitive: false,
            placeholder: "Select Chapter",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_bcbp_chapter'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.chapter_name, text: item.chapter_name };
                        })
                    };
                },
            }
        });

        $("#fa-form #batch_select2").select2({
            casesentitive: false,
            placeholder: "Select batch",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_bcbp_batch'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.batch_name, text: item.batch_name };
                        })
                    };
                },
            }
        });

        
        $("#fa-form #unit_select2").select2({
            casesentitive: false,
            placeholder: "Select unit",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_bcbp_unit'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.unit_name, text: item.unit_name };
                        })
                    };
                },
            }
        });

        $("#fa-form #group_select2").select2({
            casesentitive: false,
            placeholder: "Select action group",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_bcbp_group'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.group_name, text: item.group_name };
                        })
                    };
                },
            }
        });

        $("#fa-form #position_select2").select2({
            casesentitive: false,
            placeholder: "Select position",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_bcbp_position'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.position, text: item.position };
                        })
                    };
                },
            }
        });


        $("#fa-form #gender_select2").select2({
            casesentitive: false,
            placeholder: "Select gender",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_bcbp_gender'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.gender, text: item.gender };
                        })
                    };
                },
            }
        });


        $("#fa-form #chapter_select2").on("change", function () {
            self.setFormPropValue('chapterName', $(this).val());
        });

        $("#fa-form #batch_select2").on("change", function () {
            self.setFormPropValue('batchName', $(this).val());
        });

        $("#fa-form #gender_select2").on("change", function () {
            self.setFormPropValue('gender', $(this).val());
        });

        $("#fa-form #position_select2").on("change", function () {
            self.setFormPropValue('position', $(this).val());
        });

        $("#fa-form #group_select2").on("change", function () {
            self.setFormPropValue('groupName', $(this).val());
        });

        $("#fa-form #unit_select2").on("change", function () {
            self.setFormPropValue('unitName', $(this).val());
        });
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

    openNewProfileModal: function () {
        var self = this;

        self.setState({ showNewProfileModal: true });
    },

    closeNewProfileModal: function () {
        var self = this;

        self.setState({ showNewProfileModal: false });
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;

        console.log("form data");
        console.log(self.state.form.data);

        data.id = this.props.id;

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_bcbp_patch_profile"),
            data: data,
            type: 'PATCH'
        }).done(function (res) {
            self.reset();
            self.props.onHide();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    }
});


window.BcbpEditModal = BcbpEditModal;