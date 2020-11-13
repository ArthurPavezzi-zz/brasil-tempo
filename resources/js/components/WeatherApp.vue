<template>
  <div class="container text-white mb-8">
    <div class="location-input text-gray-800">
      <input type="search" id="address" class="form-control w-full" placeholder="Procure uma cidade">
      <p hidden>Selecionado: <strong id="address-value">none</strong></p>
    </div>
    <div class="loading" v-if="!dailyWeather.length">
      <loading-progress indeterminate size="64" rotate fillDuration="2" rotationDuration="1"/>
    </div>
    <div v-else
         class="weather-container font-sans w-128 max-w-lg rounded-lg overflow-hidden bg-gray-900 shadow-ld mt-4">
      <div class="current-weather flex items-center justify-between px-6 py-8">
        <div class="flex items-center">
          <div class="w-1/2 current-temp">
            <div class="text-4xl font-semibold">Máx: {{ dailyWeather[0].max }}º</div>
            <div>Mínima de {{ dailyWeather[0].min }}º</div>
          </div> <!-- current-temp -->
          <div class="w-1/2 mx-5">
            <div class="font-semibold">{{ dailyWeather[0].description }}</div>
            <div>{{ location.city }} - {{ location.state }}</div>
          </div>
        </div>
        <div class="weather-image">
          <img :src="dailyWeather[0].icon" alt="icone" width="150" height="150">
        </div> <!-- weather-image -->
      </div> <!-- current-weather -->
      <div class="future-weather py-2 text-sm bg-gray-800 px-6 overflow-hidden"
           v-for="(weather, index) in dailyWeather" :key="index" v-if="index > 0">
        <div class="flex items-center">
          <div class="w-1/7 text-lg text-gray-200">{{ weather.day }}</div>
          <div class="w-5/7 inline-flex items-center">
            <div><img :src="weather.icon" alt="" width="90" height="90"></div>
            <div class="ml-3">{{ weather.description }}</div>
          </div>
          <div class="w-1/7 text-right future-max-min">
            <div>{{ weather.max }}º</div>
            <div>{{ weather.min }}º</div>
          </div> <!-- future-max-min -->
        </div>
      </div> <!-- future-weather -->
    </div> <!-- weather-container -->
  </div> <!-- container -->
</template>

<script>
import moment from 'moment';

const axios = require('axios').default;

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
    changeLocaleToPtBR() {
      moment.locale('pt-br');
    },
    fetchCityCode() {
      axios.get(`/api/city-code?stateCode=${this.location.stateCode}&cityName=${this.location.city}`)
        .then(response => response.json())
        .then(data => {
          this.setCityCode(data.id)
        })
    },
    fetchWeatherData() {
      axios.get(`api/weather/?geoCode=${this.location.cityCode}`)
        .then(response => response.json())
        .then(data => {
          this.pushDailyWeather(data);
        });
    },
    fetchStateCode() {
      axios.get(`/api/state-code?name=${this.location.name}`)
        .then(response => response.json())
        .then(data => {
          this.setStateCode(data.id);
          this.setStateName(data.sigla);
        });
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

      let $address = document.querySelector('#address-value');

      placesAutoComplete.on('change', (e) => {
        if (!this.isInBrazil(e.suggestion.country)) {
          alert("Erro! Localização pesquisada fora do território nacional. Por favor, selecione um município brasileiro")
        } else {
          $address.textContent = e.suggestion.value;
          this.fetchStateCode();
          this.setCityName(e.suggestion.name);
          this.fetchCityCode();
          this.dailyWeather = [];
        }
      });

      placesAutoComplete.on('clear', function () {
        $address.textContent = 'none';
      });
    },
    pushDailyWeather(data) {
      this.changeLocaleToPtBR();
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
.location-input {
  display: flex;
  border-radius: 10px;
  width: 50%;
  margin: 0 auto;
}

.location-input input {
  padding-left: 10px;
}

.vue-progress-path {
  display: flex;
  margin: 180px auto;
}

.weather-container {
  margin-right: auto;
  margin-left: auto;
}

.weather-image {
  right: 0;
  margin-right: 0;
}

.future-max-min {
  margin: 0 0 0 auto;
}
</style>
