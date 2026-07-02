class PeriodsBlock {
    constructor(array, data = null, entrata = null) {
        this.arrayBlocchi = JSON.parse(array);

        const date = new Date();

        let day = date.getDate();
        let month = date.getMonth() + 1;
        let year = date.getFullYear();

        this.dataBaseEnd = year+"-"+month+"-"+day;
        this.dataBaseStart = data;

        this.date_result = null;
        this.totalDay = null;

        this.entry = entrata;
    }

    set startDate(startDate){
        //console.log("start inside --> "+startDate);
        this.dataBaseStart = startDate;
    }

    set entrata(entr) {
        this.entry = entr;
    }

    get intervall() {
        return this.totalDay;
    }

    get datePlusInterval(){

        if(this.dataBaseStart != null && this.totalDay != null )
            return this.addDays();
    }

    Start(){
        this.getInterval();
    }

    getInterval(){

        if(this.dataBaseStart != null) {
            //console.log("start --> "+this.arrayBlocchi.length)
            const confrontStart = new Date(this.dataBaseStart);
            const confrontEnd = new Date(this.dataBaseEnd);
            this.totalDay = 0;

            for (var i = 0; i < this.arrayBlocchi.length; i++) {

                const blockStart = new Date(this.arrayBlocchi[i].Start_Date);
                const blockEnd = new Date(this.arrayBlocchi[i].End_Date);

                var checkEntry = -1;
                if(this.entry == "CDS") checkEntry = 3;
                else checkEntry = 2;

                if(confrontStart > blockEnd || confrontEnd < blockStart || checkEntry == this.arrayBlocchi[i].Lockup_Type_Id)
                    continue;

                var startLimit = null;
                var endLimit = null;

                if(confrontStart < blockStart) startLimit = blockStart;
                else startLimit = confrontStart;

                if(confrontEnd < blockEnd) endLimit = confrontEnd;
                else endLimit = blockEnd;


                const diffTime = Math.abs(endLimit - startLimit);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                //console.log("differenza giorni --> "+diffDays)

                this.totalDay += diffDays;
                //console.log("CC --> "+this.arrayBlocchi[i].CC);
            }
        }
        else return null;
    }

    addDays(customIntervall = null) {
        var result = new Date(this.dataBaseStart);
        if(customIntervall != null) result.setDate(result.getDate() + customIntervall);
        else result.setDate(result.getDate() + this.totalDay);
        return result.toLocaleDateString("fr-CA");
    }
}