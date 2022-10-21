function getFromPLOS(search) {
  return new Promise((resolve, reject) => {
    fetch('http://api.plos.org/search?q=title:' + search)
      .then((response) => response.json())
      .then((data) => {
        var max_score = data.response.maxScore;
        var items = data.response.docs.map((item) => {
          var temp = item;
          item.type = 'PLOSAPI';
          item.n_score = item.score / max_score;
          return temp;
        });
        return resolve(items);
      })
      .catch((error) => reject(error));
  });
}

function getFromEuropean(search) {
  return new Promise((resolve, reject) => {
    fetch(
      'https://api.europeana.eu/record/v2/search.json?reusability=open&media=true&wskey=actocksh' +
        '&query=' +
        search
    )
      .then((response) => response.json())
      .then((data) => {
        var max_score = 0;
        var items = data.items;

        if (items && items[0].score) {
          max_score = items[0].score;
        }

        items = data.items.map((item) => {
          var temp = item;
          item.type = 'EuropeanAPI';
          item.n_score = item.score / max_score;
          return temp;
        });

        return resolve(items);
      })
      .catch((error) => reject(error));
  });
}
function parseJSON(response) {
  return response.text().then(function (text) {
    return text ? JSON.parse(text) : {};
  });
}
function searchTerms(query) {
  return new Promise((resolve, reject) => {
    fetch('https://api.datamuse.com/words?ml=' + query)
      .then((res) => res.json())
      .then((data) => resolve(data.map((e) => e.word)))
      .catch((err) => reject(err));
  });
}

function getCardPLOS(doc) {
  return `<div class="p-2 flex-fill bd-highlight">
                <div class="thumb">
                  <img src="img/plos_api.png" alt="" />
                  
                  <div class="text-content">
                    <h4>${doc.title_display}</h4>
                    <a href=${doc.id}>go to website</a>
                    <p>score: ${doc.score}</p>
                    <p>Normalized score: ${doc.n_score}</p>
                  </div>

                  <div class="plus-button">
                    <a href="#"><i class="fa fa-plus"></i></a>
                  </div>
                </div>
              </div>`;
}

function getCardEuropean(doc) {
  return `<div class="p-2 flex-fill bd-highlight">
                <div class="thumb">
                  <img src="img/europeana_api.png" alt="" />
                  
                  <div class="text-content">
                    <h4>${doc.title[0]}</h4>
                    <a href=${doc.id}>go to website</a>
                    <p>score: ${doc.score}</p>
                    <p>Normalized score: ${doc.n_score}</p>
                  </div>

                  <div class="plus-button">
                    <a href="#"><i class="fa fa-plus"></i></a>
                  </div>
                </div>
              </div>`;
}
function searchData() {
  var query = document.getElementById('query').value;

  var responsePromises = getResponseFromAPIs(query);

  Promise.all(responsePromises)
    .then((arrays) => {
      var items = [];
      arrays.forEach((arr) => {
        items = items.concat(arr);
      });
      console.log(items);
      return printData(items);
    })
    .catch((error) => {
      alert(error);
    });
}

function getResponseFromAPIs(query) {
  var isWithEuropean = document.getElementById('europeanaCB').checked;
  var isWithPLOS = document.getElementById('PLOSCB').checked;

  var promises = [];

  console.log(isWithEuropean);
  console.log(isWithPLOS);

  if (isWithEuropean) promises.push(getFromEuropean(query));
  if (isWithPLOS) promises.push(getFromPLOS(query));

  return promises;
}

function printData(items) {
  sortedItems = items.sort((a, b) =>
    a.n_score < b.n_score ? 1 : a.n_score > b.n_score ? -1 : 0
  );
  cardsDocs = '';
  sortedItems.forEach((item) => {
    if (item.type == 'EuropeanAPI') {
      cardsDocs += getCardEuropean(item);
    } else {
      cardsDocs += getCardPLOS(item);
    }
  });

  cardsContainerElement = document.getElementById('cards-container');
  cardsContainerElement.innerHTML = cardsDocs;
}

async function expandTerms(input) {
  var query = input.value;
  searchTerms(query).then((words) => {
    autocomplete(input, words);
    console.log(words);
  });
}

/**AUTOCOMPLETE */
function autocomplete(inp, arr) {
  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus;
  /*execute a function when someone writes in the text field:*/

  var a,
    b,
    i,
    val = inp.value;
  console.log(val);
  if (!val) {
    return false;
  }
  closeAllLists(inp);
  currentFocus = -1;
  /*create a DIV element that will contain the items (values):*/
  a = document.createElement('DIV');
  a.setAttribute('id', inp.id + 'autocomplete-list');
  a.setAttribute('class', 'autocomplete-items');
  /*append the DIV element as a child of the autocomplete container:*/
  inp.parentNode.appendChild(a);
  /*for each item in the array...*/
  for (i = 0; i < arr.length; i++) {
    /*check if the item starts with the same letters as the text field value:*/
    if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
      /*create a DIV element for each matching element:*/
      b = document.createElement('DIV');
      /*make the matching letters bold:*/
      b.innerHTML = '<strong>' + arr[i].substr(0, val.length) + '</strong>';
      b.innerHTML += arr[i].substr(val.length);
      /*insert a input field that will hold the current array item's value:*/
      b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
      /*execute a function when someone clicks on the item value (DIV element):*/
      b.addEventListener('click', function (e) {
        /*insert the value for the autocomplete text field:*/
        inp.value = this.getElementsByTagName('input')[0].value;
        /*close the list of autocompleted values,
              (or any other open lists of autocompleted values:*/
        closeAllLists();
      });
      a.appendChild(b);
    }
  }
}

/*execute a function presses a key on the keyboard:*/
function addEventListener(e) {
  var x = document.getElementById(this.id + 'autocomplete-list');
  if (x) x = x.getElementsByTagName('div');
  if (e.keyCode == 40) {
    /*If the arrow DOWN key is pressed,
        increase the currentFocus variable:*/
    currentFocus++;
    /*and and make the current item more visible:*/
    addActive(x);
  } else if (e.keyCode == 38) {
    //up
    /*If the arrow UP key is pressed,
        decrease the currentFocus variable:*/
    currentFocus--;
    /*and and make the current item more visible:*/
    addActive(x);
  } else if (e.keyCode == 13) {
    /*If the ENTER key is pressed, prevent the form from being submitted,*/
    e.preventDefault();
    if (currentFocus > -1) {
      /*and simulate a click on the "active" item:*/
      if (x) x[currentFocus].click();
    }
  }
}

function addActive(x) {
  /*a function to classify an item as "active":*/
  if (!x) return false;
  /*start by removing the "active" class on all items:*/
  removeActive(x);
  if (currentFocus >= x.length) currentFocus = 0;
  if (currentFocus < 0) currentFocus = x.length - 1;
  /*add class "autocomplete-active":*/
  x[currentFocus].classList.add('autocomplete-active');
}
function removeActive(x) {
  /*a function to remove the "active" class from all autocomplete items:*/
  for (var i = 0; i < x.length; i++) {
    x[i].classList.remove('autocomplete-active');
  }
}
function closeAllLists(elmnt) {
  /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
  var x = document.getElementsByClassName('autocomplete-items');
  for (var i = 0; i < x.length; i++) {
    if (elmnt.parentNode == x[i].parentNode) {
      x[i].parentNode.removeChild(x[i]);
    }
  }
}
/*execute a function when someone clicks in the document:*/
document.addEventListener('click', function (e) {
  closeAllLists(e.target);
});