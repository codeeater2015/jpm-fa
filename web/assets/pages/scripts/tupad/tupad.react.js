var TupadComponent = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            form: {
                data: {
                    municipalityNo: null,
                    municipalityName: null,
                    barangayNo: null,
                    barangayName: null,
                    serviceType: null,
                    source : null
                }
            }
        }
    },

    componentDidMount: function () {
        this.initSelect2();
    },

    initSelect2: function () {
        var self = this;

        $("#tupad_component #municipality_select2").select2({
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
                        provinceCode: 53
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

        $("#tupad_component #barangay_select2").select2({
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
                        municipalityNo: $("#tupad_component #municipality_select2").val(),
                        provinceCode: 53
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

        $("#tupad_component #municipality_select2").on("change", function () {
            self.setFormPropValue('municipalityNo', $(this).val());
            self.loadMunicipality($(this).val());
        });

        $("#tupad_component #barangay_select2").on("change", function () {
            self.setFormPropValue('barangayNo', $(this).val());
            self.loadBarangay(self.state.form.data.municipalityNo, $(this).val());
        });
    },

    loadMunicipality: function (municipalityNo) {
        var self = this;

        self.requestMunicipality = $.ajax({
            url: Routing.generate("ajax_get_municipality_loc", { municipalityNo }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;
            form.data.municipalityName = res.name;
            self.setState({ form: form }, self.reload);
        }).fail(function () {
            var form = self.state.form;
            form.data.municipalityName = null;
            self.setState({ form: form });
        });
    },

    loadBarangay: function (municipalityNo, barangayNo) {
        var self = this;

        self.requestMunicipality = $.ajax({
            url: Routing.generate("ajax_get_barangay_loc", { municipalityNo: municipalityNo, brgyNo: barangayNo }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;
            form.data.barangayName = res.name;
            self.setState({ form: form }, self.reload);
        }).fail(function () {
            var form = self.state.form;
            form.data.barangayName = null;
            self.setState({ form: form });
        });;
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

    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    closeCreateModal: function () {
        this.setState({ showCreateModal: false });
    },

    setFormPropValue: function (field, value) {
        var form = this.state.form;
        form.data[field] = value;
        this.setState({ form: form });
    },

    setFormProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.value;

        console.log("e.target.name", e.target.name);
        console.log(' value', e.target.value);

        this.setState({ form: form }, this.reload);
    },

    reload: function () {
        this.refs.tupadDatatableReference.reload();
    },

    render: function () {
        var self = this;

        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body" id="tupad_component">

                    {
                        this.state.showCreateModal &&
                        <TupadCreateModal
                            proId={3}
                            show={this.state.showCreateModal}
                            notify={this.props.notify}
                            reload={this.reload}
                            onHide={this.closeCreateModal}
                            serviceType={this.state.form.data.serviceType}
                            source={this.state.form.data.source}
                            municipalityNo={this.state.form.data.municipalityNo}
                            municipalityName={this.state.form.data.municipalityName}
                            barangayName={this.state.form.data.barangayName}
                            barangayNo={this.state.form.data.barangayNo}
                        />
                    }

                    <div className="row">
                        <div className="col-md-2">
                            <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityName">
                            </select>
                        </div>
                        <div className="col-md-2">
                            <select id="barangay_select2" className="form-control form-filter input-sm" name="barangayName">
                            </select>
                        </div>
                        <div className="col-md-2">
                            <select className="form-control" onChange={this.setFormProp} value={this.state.form.data.serviceType} name="serviceType">
                                <option value=""> - SELECT TYPE OF SERVICE - </option>
                                <option value="AICS_FOOD">Food Assistance</option>
                                <option value="SLP">Sustainable Livelihood Program(SLP)</option>
                                <option value="AICS_EDUC">DSWD-AICS Educational Assistance</option>
                                <option value="TUPAD">Tulong Panghanapbuhay (Disadvantage /Displaced Workers)</option>
                            </select>
                        </div>

                        <div className="col-md-2">
                            <select className="form-control" onChange={this.setFormProp} value={this.state.form.data.source} name="source">
                                <option value=""> - SELECT SOURCE - </option>
                                <option value="VICE">VICE OLA / PROVINCE</option>
                                <option value="SIR GING">SIR GING</option>
                                <option value="SIR TON">SIR TON</option>
                            </select>
                        </div>

                        <div className="col-md-2">
                            <input type="date"  className="input-md form-control"  name="release_date" />
                        </div>

                        <div className="col-md-2">
                            <button type="button" className="btn btn-primary" onClick={this.openCreateModal}>Add Record</button>
                        </div>
                    </div>

                    <div className="row">
                        <TupadDatatable
                            sourceMunicipality={self.state.form.data.municipalityName}
                            sourceBarangay={self.state.form.data.barangayName}
                            serviceType={self.state.form.data.serviceType}
                            source={this.state.form.data.source}
                            ref="tupadDatatableReference" />
                    </div>
                </div>
            </div>
        )
    }
});

setTimeout(function () {
    ReactDOM.render(
        <TupadComponent />,
        document.getElementById('page-container')
    );
}, 500);
