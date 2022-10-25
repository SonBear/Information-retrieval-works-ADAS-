function getFromPLOS(search) {
  return new Promise((resolve, reject) => {
    fetch('http://api.plos.org/search?q=' + search)
      .then((response) => response.json())
      .then((data) => {
        if (data.response == undefined) return resolve([]);
        let max_score = data.response.maxScore;
        let items = data.response.docs.map((item) => {
          let temp = {};
          temp.type = 'PLOSAPI';
          temp.n_score = item.score / max_score;
          temp.score = item.score;
          temp.url = 'https://journals.plos.org/plosone/article?id=' + item.id;
          temp.title = item.title_display;
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
        let max_score = 0;
        let items = data.items;

        if (items == undefined) return resolve([]);

        if (items.length > 0 && items[0].score) max_score = items[0].score;

        items = data.items.map((item) => {
          let temp = {};
          temp.type = 'EuropeanAPI';
          temp.url = 'https://www.europeana.eu/es/item' + item.id;
          temp.n_score = item.score / max_score;
          temp.score = item.score;
          temp.title = item.title[0];
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
function searchTerms(query, number) {
  return new Promise((resolve, reject) => {
    fetch('https://api.datamuse.com/words?ml=' + query)
      .then((res) => res.json())
      .then((data) => resolve(data.map((e) => e.word).slice(0, number)))
      .catch((err) => reject(err));
  });
}

function getCardPLOS(doc) {
  return `<div class="p-2 flex-fill bd-highlight">
                <div class="thumb">
                  <img src="img/plos_api.png" alt="" />
                  
                  <div class="text-content">
                    <h4>${doc.title}</h4>
                    <a href="${doc.url}" target="_blank">go to website</a>
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
                    <h4>${doc.title}</h4>
                    <a href="${doc.url}" target="_blank">go to website</a>
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
  getAllQueries().then((query) => {
    let responsePromises = getResponseFromAPIs(query);

    Promise.all(responsePromises)
      .then((arrays) => {
        let items = [];
        arrays.forEach((arr) => {
          items = items.concat(arr);
        });
        return printData(items);
      })
      .catch((error) => {
        alert(error);
      });
  });
}

function getAllQueries() {
  return new Promise(async (resolve, reject) => {
    let queryInputs = [...document.getElementsByClassName('query-input')].map(
      (e) => e.value
    );

    queryInputs = queryInputs.filter((a) => a != '');

    if (queryInputs.length == 1) {
      let query = queryInputs[0];
      const terms = await searchTerms(query, 5);
      let termsQuery = '';
      if (terms.length > 0) {
        terms.forEach((x) => (termsQuery += ` OR '${x}'`));
        return resolve(`'${query}'` + termsQuery);
      } else {
        return resolve(query);
      }
    }

    if (queryInputs.length > 1) {
      var query = '';
      for (let idx = 0; idx < queryInputs.length; idx++) {
        let a = queryInputs[idx];
        const terms = await searchTerms(a, 5);

        let termsQuery = '';
        if (terms.length > 0) {
          terms.forEach((x) => (termsQuery += ` OR '${x}'`));
          query += `'${a}'` + termsQuery;
        } else {
          query += `'${a}'`;
        }

        if (idx < queryInputs.length - 1) query += ' AND ';
      }

      console.log(query);
      return resolve(query);
    }

    return reject(error);
  });
}

function getResponseFromAPIs(query) {
  let isWithEuropean = document.getElementById('europeanaCB').checked;
  let isWithPLOS = document.getElementById('PLOSCB').checked;

  let promises = [];

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
  let query = input.value;
  searchTerms(query, 10).then((words) => {
    autocomplete(input, words);
  });
}

let id_input = 0;
function addInputSearch(btn) {
  let inputContainer = document.getElementById('input-container');

  let p = document.createElement('div');
  p.className = 'col-md-1 label-and';
  p.id = 'AND-' + id_input;
  p.innerHTML = '<p>AND</p>';

  inputContainer.appendChild(p);
  let input = document.createElement('div');
  input.className = 'col-md-3';
  input.id = 'input-' + id_input++;
  input.innerHTML = getInputSearch();

  inputContainer.appendChild(input);
}

function deleteInput(btn) {
  let inputContainer = document.getElementById('input-container');

  let input = btn.parentNode.parentNode;

  let id_arr = input.id.split('-');

  let p = document.getElementById('AND-' + id_arr[1]);
  inputContainer.removeChild(input);
  inputContainer.removeChild(p);

  id_input--;
}

function getInputSearch() {
  return `    <div class="row input-term">

            <input
              name="search"
              type="text"
              class="form-control col-md-6 query-input"
              placeholder="search..."
              required="true"
              oninput="expandTerms(this)"
            />
            <button onclick="deleteInput(this)" class="col-md-1">x</button>
          </div>`;
}

/**AUTOCOMPLETE --REFERENCE W3SCHOOL*/
function autocomplete(inp, arr) {
  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  let currentFocus;
  /*execute a function when someone writes in the text field:*/

  let a,
    b,
    i,
    val = inp.value;
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
        closeAllLists(inp);
      });
      a.appendChild(b);
    }
  }
}

/*execute a function presses a key on the keyboard:*/
function addEventListener(e) {
  let x = document.getElementById(this.id + 'autocomplete-list');
  if (x) x = x.getElementsByTagName('div');
  if (e.keyCode == 40) {
    /*If the arrow DOWN key is pressed,
        increase the currentFocus letiable:*/
    currentFocus++;
    /*and and make the current item more visible:*/
    addActive(x);
  } else if (e.keyCode == 38) {
    //up
    /*If the arrow UP key is pressed,
        decrease the currentFocus letiable:*/
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
  for (let i = 0; i < x.length; i++) {
    x[i].classList.remove('autocomplete-active');
  }
}
function closeAllLists(elmnt) {
  /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
  let x = document.getElementsByClassName('autocomplete-items');
  for (let i = 0; i < x.length; i++) {
    if (elmnt.parentNode == x[i].parentNode) {
      x[i].parentNode.removeChild(x[i]);
    }
  }
}
/*execute a function when someone clicks in the document:*/
document.addEventListener('click', function (e) {
  closeAllLists(e.target);
});
