var VoterProvinceSummaryTable = React.createClass({

    getInitialState : function(){
      return {
          data : [],
          summary : {
            totalMunicipality : 0,
            totalBarangay : 0,
            totalPrecincts : 0,
            totalRegistered : 0,
            totalRecruits : 0,
            totalLeaders : 0,
            totalMembers : 0,
            totalVoted : 0,
            totalVotedRecruits : 0,
            percentage : 0
          },
          province : {
              name : ''
          },
          targetProvince : null,
          targetMunicipality : null,
          showItemDetail : false,
          loading : false,
          refreshInterval : 300000,
          refreshCounter : 0,
          refreshId : null,
          counterId : null,
          mode : 'all'
      }
    },

    componentDidMount : function(){
       var self = this;

       self.loadData();
       self.initShortcuts();

       var refreshId = setInterval(function(){
           self.loadData();
       },self.state.refreshInterval);

       self.setState({refreshId : refreshId});
    },

    componentWillUnmount : function(){
        clearInterval(this.state.refreshId);
        clearInterval(this.state.counterId);
    },

    initShortcuts : function(){
        var self = this;
        $("body").keydown(function(e){
            var keyCode = e.keyCode || e.which;
            if(keyCode == 121){
                e.preventDefault();
                self.loadData(); 
            }
        });
    },

    initCounter : function(){
        var self = this;
        var counterId = self.state.counterId;

        if(counterId != null)
            clearInterval(counterId);

        var counterId = setInterval(function(){
            var refreshCounter = self.state.refreshCounter;
            refreshCounter++;
            self.setState({refreshCounter : refreshCounter});
        },1000);
        
        self.setState({ counterId : counterId, refreshCounter : 0});
    },
    
    loadData : function(){
        this.loadProvince(this.props.provinceCode);
        this.loadProvinceData(this.props.electId, this.props.proId , this.props.provinceCode);
        this.initCounter();
    },

    loadProvince : function(provinceCode){
        var self = this;
        self.requestProvince = $.ajax({
            url : Routing.generate("ajax_get_province",{provinceCode : provinceCode}),
            type : "GET"
        }).done(function(res){
            self.setState({ province : res });
        });
    },

    loadProvinceData : function(electId, proId, provinceCode){
        var self = this;
        
        self.requestProvinceData = $.ajax({
            url : Routing.generate("ajax_get_province_data_summary",{electId : electId, proId : proId, provinceCode : provinceCode}),
            type : "GET"
        }).done(function(res){
            self.setState({data : res});
            self.setSummary(res);
        }).always(function(){
            self.setState({loading : false});
        });

        self.setState({loading : true});
    },

    setSummary : function(data){
        var totalBarangay = 0;
        var totalPrecincts = 0;
        var totalRegistered = 0;
        var totalLeaders = 0;
        var totalMembers = 0;
        var totalRecruits = 0;
        var percentage = 0;

        data.map(function(item){
            totalBarangay += parseInt(item.total_barangays);
            totalPrecincts += parseInt(item.total_precincts);
            totalRegistered += parseInt(item.total_voters);
            totalRecruits += parseInt(item.total_recruits);
            totalLeaders += parseInt(item.total_leaders);
            totalMembers += parseInt(item.total_members);
        });

        if(totalRecruits > 0)
            percentage = (totalRecruits/totalRegistered * 100).toFixed(2);
        
        var summary = {
            totalBarangay : totalBarangay,
            totalPrecincts : totalPrecincts,
            totalRegistered  : totalRegistered,
            totalRecruits : totalRecruits,
            percentage : percentage
        };

        this.setState({summary : summary});
    },

    numberWithCommas : function(x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    },

    render : function(){
        var self = this;
        var counter = 0;
        var filteredVoter = 0;
        var filteredVoted = 0;
        var filteredRecruits = 0;
        var filteredVotedRecruits = 0;
        var filteredRecruitsPercentage = 0;

        self.state.data.map(function(item){
            if(self.display(item)){
                filteredVoter += parseInt(item.total_voters);
                filteredRecruits += parseInt(item.total_recruits);
            }
        });

        filteredRecruitsPercentage = ((filteredRecruits / filteredVoter) * 100).toFixed(2);

        return (
            <div>
                {
                    self.state.showItemDetail && !self.isEmpty(self.state.targetMunicipality) &&
                    <VoterSummaryItemDetail
                        electId={self.props.electId}
                        proId={self.props.proId} 
                        provinceCode={self.state.targetProvince}
                        municipalityNo={self.state.targetMunicipality}
                        show={self.state.showItemDetail}
                        onHide={self.closeDetailModal}
                        
                        >
                    </VoterSummaryItemDetail>        
                }

                <div style={{ marginBottom: "5px" }}><strong>Province : {this.state.province.name}</strong></div>
                <div className="col-md-3" style={{padding : 0}}>
                    <div className="bold">Total Barangay : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalBarangay)}</span></div>
                    <div className="bold">Total Precincts : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalPrecincts)}</span></div>
                </div>
                <div className="col-md-3" style={{padding : 0}}>
                    <div className="bold">Registered Voter : <span className="font-red-sunglo">{this.numberWithCommas(this.state.summary.totalRegistered)}</span></div>
                    <div className="bold">Recruited Voter : <span className="font-red-sunglo"> {this.numberWithCommas(this.state.summary.totalRecruits)}</span></div>
                </div>
                <div className="col-md-3" style={{padding : 0}}>
                    <div className="bold">Percentage : <span className="font-red-sunglo"> {this.numberWithCommas(this.state.summary.percentage)} %</span> </div>
                </div>
                <div className="clearfix"></div>
                <div style={{marginTop:"10px"}}>
                    <div className="bold" style={{ marginBottom : "5px" }}>Filtered Summary: </div>
                    <div className="col-md-3" style={{padding : 0}}>
                        <div className="bold">Registered Voter : <span className="font-red-sunglo">{this.numberWithCommas(filteredVoter)}</span></div>
                        <div className="bold">Recruited Voter : <span className="font-red-sunglo"> {this.numberWithCommas(filteredRecruits)}</span></div>
                    </div>
                    <div className="col-md-3" style={{padding : 0}}>
                        <div className="bold">Percentage : <span className="font-red-sunglo">{this.numberWithCommas(filteredRecruitsPercentage)} %</span></div>
                    </div>
                </div>
                <div className="clearfix"/>
                <div className="col-md-6" style={{padding : "0"}}>
                    <select value={this.state.mode} onChange={self.setMode} style={{ marginTop : "8px"}}>
                        <option value="all"> All</option>
                        <option value="active">Active</option>
                        <option value="has_entry">Has Entry</option>
                        <option value="no_entry">No Entry</option>
                    </select> 
                </div>
                <div className="col-md-6 text-right" style={{marginTop:"10px" , marginBottom : "5px", padding : "0"}}>
                    <small>Next refresh : <span className="bold font-green-jungle">{(this.state.refreshInterval / 1000)  - this.state.refreshCounter}s</span> </small>
                    <button className="btn btn-primary btn-xs" onClick={this.loadData}><i className="fa fa-refresh"></i> Refresh (F10)</button>
                </div>
                <div className="table-container">
                    <table id="voter_summary_table" className="table table-striped table-bordered" width="100%" >
                        <thead  className="bg-blue-dark font-white">
                            <tr>
                                <th className="text-center" rowSpan="2">#</th>
                                <th className="text-center" rowSpan="2">Mun</th>
                                <th className="text-center" rowSpan="2">Brgy</th>
                                <th className="text-center" rowSpan="2">Prec</th>
                                <th className="text-center" rowSpan="2">Reg</th>
                                <th className="text-center" rowSpan="2">Voted</th>
                                <th className="text-center" colSpan="6">Recuited Voter</th>
                                <th className="text-center" rowSpan="2"></th>
                            </tr>
                            <tr>
                                <th className="text-center">L</th>
                                <th className="text-center">M</th>
                                <th className="text-center">Total</th>
                                <th className="text-center">%</th>
                                <th className="text-center">Voted</th>
                                <th className="text-center">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            {this.state.data.map(function(item,index){
                                if(self.display(item)){
                                    counter += 1;
                                    return (
                                        <tr>
                                            <td  className="text-center">{counter}</td>
                                            <td  className="text-center">
                                                {item.name}
                                                {
                                                    !self.isEmpty(item.updated_at) && 
                                                    (<span className="font-green-jungle bold" style={{ fontSize : "10px", marginLeft: "5px"}}>
                                                        {moment( new Date(item.updated_at)).fromNow()}
                                                    </span>)
                                                }
                                            </td>
                                            <td  className="text-center">{item.total_barangays == 0 ? "- - -" : self.numberWithCommas(item.total_barangays)  }</td>
                                            <td  className="text-center">{item.total_precincts == 0 ? "- - -" : self.numberWithCommas(item.total_precincts)}</td>
                                            <td  className="text-center">{item.total_voters == 0 ? "- - -" : self.numberWithCommas(item.total_voters)}</td>
                                            <td  className="text-center">{item.total_voted == 0 ? "- - -" : self.numberWithCommas(item.total_voted)}</td>
                                            <td  className="text-center">{item.total_leaders == 0 ? "- - -" : self.numberWithCommas(item.total_leaders)}</td>
                                            <td  className="text-center">{item.total_members == 0 ? "- - -" : self.numberWithCommas(item.total_members)}</td>
                                            <td  className="text-center">{item.total_recruits == 0 ? "- - -" : self.numberWithCommas(item.total_recruits)}</td>
                                            <td  className="text-center">{item.total_recruits == 0 ? "- - -" : (item.total_recruits / item.total_voters * 100).toFixed(2) + '%'}</td>
                                            <td  className="text-center">{item.total_voted_recruits == 0 ? "- - -" : self.numberWithCommas(item.total_voted_recruits)}</td>
                                            <td  className="text-center">{item.total_voted_recruits == 0 ? "- - -" : (item.total_voted_recruits / item.total_recruits * 100).toFixed(2) + '%'}</td>
                                            <td className="text-center">
                                                <button className="btn btn-primary btn-xs" type="button" onClick={self.showDetail.bind(self,item)}>
                                                    <small>Details</small>
                                                </button>
                                                
                                            </td>
                                        </tr>
                                    );
                                }
                            })}

                            { this.state.data.length == 0 && !this.state.loading &&
                                <tr>
                                    <td className="text-center" colSpan="13">No records was found...</td>
                                </tr>
                            }
                            { this.state.loading &&
                                <tr>
                                    <td className="text-center" colSpan="13">Data is being processed. Please wait for a while...</td>
                                </tr>
                            }
                        </tbody>
                    </table>
                </div>
            </div>
        )
    },

    setMode : function(e){
        this.setState({mode : e.target.value});        
    },
    
    showDetail : function(item){
        var self = this;
        self.setState({ 
            targetProvince : self.props.provinceCode,
            targetMunicipality : item.municipality_no,
            showItemDetail : true
        });
    },

    closeDetailModal : function(){
        this.setState({showItemDetail :false, targetMunicipality : null});
    },

    isEmpty : function(value){
        return value == null || value == '' || value == 'undefined';
    },

    display : function(item){
        var mode = this.state.mode;

        if(mode == 'all'){
            return true;
        }else if(mode == 'active'){
            return this.isActive(item.updated_at);
        }else if(mode == 'no_entry'){
            return item.total_recruits == 0;
        }else if(mode == 'has_entry'){
            return item.total_recruits > 0;
        }

        return false;
    },

    isActive : function(updatedAt){
        if(this.isEmpty(updatedAt))
            return false;

        var today = new Date();
        var lastUpdate = new Date(updatedAt);
        var diffMs = (today  - lastUpdate);
        var diffDays = Math.floor(diffMs / 86400000); // days
        var diffHrs = Math.floor((diffMs % 86400000) / 3600000);
        var diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes
        
        if(today.getTime() < lastUpdate.getTime())
            return true;
            
        return diffDays == 0  &&  diffHrs == 0 && diffMins <= 10;
    }

});

window.VoterProvinceSummaryTable = VoterProvinceSummaryTable;