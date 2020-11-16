<template>
  <div class="container text-white">
    <div class="location-input mx-6 md:mx-auto w-auto lg:w-1/2 my-0 text-gray-800">
      <input type="search" id="address" class="form-control rounded-3xl pl-4" placeholder="Procure uma cidade">
    </div>
    <div class="loading" v-if="!dailyWeather.length">
      <loading-progress indeterminate size="64" rotate fillDuration="2" rotationDuration="1"/>
    </div>
    <div v-else
         class="weather-container mx-auto font-sans w-10/12 w-auto sm:w-128 max-w-lg rounded-lg overflow-hidden bg-gray-900 shadow-ld mt-4">
      <div class="current-weather flex items-center justify-between px-6 py-8">
        <div class="flex items-center">
          <div class="w-1/2 current-temp">
            <p class="text-4xl font-semibold">Máx: {{ dailyWeather[0].max }}º</p>
            <p>Mínima de {{ dailyWeather[0].min }}º</p>
          </div> <!-- current-temp -->
          <div class="w-1/2 mx-5">
            <p class="font-semibold">{{ dailyWeather[0].description }}</p>
            <p>{{ location.city }} - {{ location.state }}</p>
          </div>
        </div>
        <div class="weather-image mr-0 right-0">
          <img :src="dailyWeather[0].icon" alt="icone" width="150" height="150">
        </div> <!-- weather-image -->
      </div> <!-- current-weather -->
      <div class="future-weather py-2 text-sm bg-gray-800 px-6 overflow-hidden"
           v-for="(weather, index) in dailyWeather" :key="index" v-if="index > 0">
        <div class="flex items-center">
          <p class="w-1/7 text-lg text-gray-200">{{ weather.day }}</p>
          <div class="w-5/7 inline-flex items-center">
            <img :src="weather.icon" alt="" width="80" height="80">
            <p class="ml-3">{{ weather.description }}</p>
          </div>
          <div class="future-max-min ml-auto my-0 mr-0 w-1/7 text-right">
            <p>{{ weather.max }}º</p>
            <p>{{ weather.min }}º</p>
          </div> <!-- future-max-min -->
        </div>
      </div> <!-- future-weather -->
    </div> <!-- weather-container -->
  </div> <!-- container -->
</template>

<script>
import moment from 'moment';
import fetch from 'node-fetch';

export default {
  data() {
    return {
      dailyWeather: [],
      location: {
        city: 'Curitiba',
        state: 'PR',
        cityCode: 4106902,
        stateCode: 41
      }
    }
  },
  methods: {
    changeLocaleToPtBr() {
      moment.locale('pt-br');
    },
    async fetchCityCode() {
      await fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${this.location.stateCode}/municipios`)
        .then(response => response.json())
        .then(data => {
          let city = data.filter(city => city.nome === this.location.city);
          this.setCityCode(city[0].id);
        })
    },
    fetchWeatherData() {
      this.dailyWeather = [];
      fetch(`https://apiprevmet3.inmet.gov.br/previsao/${this.location.cityCode}`)
        .then(response => response.json())
        .then(data => {
          this.pushDailyWeather(data);
        });
    },
    async fetchStateCode(stateName) {
      await fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/`)
        .then(response => response.json())
        .then(data => {
          let state = data.filter(state => state.nome === stateName)
          this.setStateCode(state[0].id);
          this.setStateName(state[0].sigla);
        });
    },
    async handleCityChange(location) {
      await this.fetchStateCode(location.administrative);
      await this.setCityName(location.name);
      await this.fetchCityCode();
    },
    handleSearch() {
      const placesAutoComplete = places({
        appId: 'pl18AZXL29NF',
        apiKey: '429225c6c5ce22da01c723087ed19453',
        container: document.querySelector('#address')
      }).configure({
        type: 'city',
        aroundLatLngViaIp: false
      });

      placesAutoComplete.on('change', (e) => {
        if (!this.isInBrazil(e.suggestion.country)) {
          alert("Erro! Localização pesquisada fora do território nacional. Por favor, selecione um município brasileiro")
        } else {
          this.handleCityChange(e.suggestion);

        }
      });
    },
    pushDailyWeather(data) {
      this.changeLocaleToPtBr();
      let currentMoment = moment();
      const fiveDaysAhead = moment().add(5, 'days')
      const weather = data[this.location.cityCode];

      while (currentMoment.isBefore(fiveDaysAhead, 'day')) {
        let currentDayFormatted = currentMoment.format('DD/MM/YYYY');

        // por algum motivo que só Deus sabe, os objetos da lista de previsões não são identicos
        // esse if else foi a minha solução para esse problema
        let currentDayWeather = weather[currentDayFormatted];
        if (currentDayWeather.manha) {
          this.dailyWeather.push({
            day: currentMoment.format('ddd').toUpperCase(),
            max: currentDayWeather.manha.temp_max,
            min: currentDayWeather.manha.temp_min,
            description: currentDayWeather.manha.resumo,
            icon: currentDayWeather.manha.icone,
          });
        } else {
          this.dailyWeather.push({
            day: currentMoment.format('ddd').toUpperCase(),
            max: currentDayWeather.temp_max,
            min: currentDayWeather.temp_min,
            description: currentDayWeather.resumo,
            icon: currentDayWeather.icone,
          });
        }
        currentMoment.add(1, 'days');
      }
    },
    setStateCode(stateCode) {
      this.location.stateCode = stateCode;
    },
    setStateName(stateName) {
      this.location.state = stateName
    },
    setCityCode(cityCode) {
      this.location.cityCode = cityCode;
    },
    setCityName(cityName) {
      this.location.city = cityName;
    },
    isInBrazil(location) {
      return location === "Brasil";
    }
  },
  mounted() {
    this.fetchWeatherData();
    this.handleSearch();
  },
  watch: {
    location: {
      handler(newValue, oldValue) {
        this.fetchWeatherData()
      },
      deep: true
    }
  }
}
</script>
<style scoped>
.vue-progress-path {
  display: flex;
  margin: 180px auto;
}
</style>
