/**
 * Function registry for the formula engine
 * Exports all available functions and metadata
 */

import { mathFunctions } from './math.js'
import { textFunctions } from './text.js'
import { logicFunctions } from './logic.js'
import { arrayFunctions } from './array.js'

/**
 * All available functions
 */
export const functions = {
  ...mathFunctions,
  ...textFunctions,
  ...logicFunctions,
  ...arrayFunctions
}

/**
 * Function metadata for documentation and autocomplete
 */
export const functionMeta = {
  // Math functions
  SUM: {
    category: 'math',
    signature: 'SUM(value1, value2, ...)',
    description: 'Soma todos os números',
    examples: ['SUM(1, 2, 3) → 6', 'SUM({Price}, {Tax}) → total']
  },
  AVERAGE: {
    category: 'math',
    signature: 'AVERAGE(value1, value2, ...)',
    description: 'Retorna a média aritmética dos valores',
    examples: ['AVERAGE(1, 2, 3) → 2', 'AVERAGE({Score1}, {Score2}) → mean']
  },
  MIN: {
    category: 'math',
    signature: 'MIN(value1, value2, ...)',
    description: 'Retorna o menor valor',
    examples: ['MIN(5, 3, 8) → 3', 'MIN({Price1}, {Price2}) → lowest']
  },
  MAX: {
    category: 'math',
    signature: 'MAX(value1, value2, ...)',
    description: 'Retorna o maior valor',
    examples: ['MAX(5, 3, 8) → 8', 'MAX({Score1}, {Score2}) → highest']
  },
  ROUND: {
    category: 'math',
    signature: 'ROUND(number, decimals?)',
    description: 'Arredonda um número para as casas decimais indicadas',
    examples: ['ROUND(3.7) → 4', 'ROUND(3.14159, 2) → 3.14']
  },
  FLOOR: {
    category: 'math',
    signature: 'FLOOR(number)',
    description: 'Arredonda para baixo, até o inteiro mais próximo',
    examples: ['FLOOR(3.7) → 3', 'FLOOR(-2.1) → -3']
  },
  CEIL: {
    category: 'math',
    signature: 'CEIL(number)',
    description: 'Arredonda para cima, até o inteiro mais próximo',
    examples: ['CEIL(3.2) → 4', 'CEIL(-2.9) → -2']
  },
  ABS: {
    category: 'math',
    signature: 'ABS(number)',
    description: 'Retorna o valor absoluto',
    examples: ['ABS(-5) → 5', 'ABS(3) → 3']
  },
  MOD: {
    category: 'math',
    signature: 'MOD(number, divisor)',
    description: 'Retorna o resto da divisão',
    examples: ['MOD(10, 3) → 1', 'MOD(7, 2) → 1']
  },
  POWER: {
    category: 'math',
    signature: 'POWER(base, exponent)',
    description: 'Eleva um número a uma potência',
    examples: ['POWER(2, 3) → 8', 'POWER(10, 2) → 100']
  },
  SQRT: {
    category: 'math',
    signature: 'SQRT(number)',
    description: 'Retorna a raiz quadrada',
    examples: ['SQRT(16) → 4', 'SQRT(2) → 1.414...']
  },

  // Text functions
  CONCAT: {
    category: 'text',
    signature: 'CONCAT(text1, text2, ...)',
    description: 'Junta textos',
    examples: ['CONCAT("Hello", " ", "World") → "Hello World"', 'CONCAT({First}, " ", {Last}) → full name']
  },
  UPPER: {
    category: 'text',
    signature: 'UPPER(text)',
    description: 'Converte o texto para maiúsculas',
    examples: ['UPPER("hello") → "HELLO"']
  },
  LOWER: {
    category: 'text',
    signature: 'LOWER(text)',
    description: 'Converte o texto para minúsculas',
    examples: ['LOWER("HELLO") → "hello"']
  },
  TRIM: {
    category: 'text',
    signature: 'TRIM(text)',
    description: 'Remove espaços no início e no fim',
    examples: ['TRIM("  hello  ") → "hello"']
  },
  LEFT: {
    category: 'text',
    signature: 'LEFT(text, count)',
    description: 'Retorna os N primeiros caracteres',
    examples: ['LEFT("Hello", 2) → "He"']
  },
  RIGHT: {
    category: 'text',
    signature: 'RIGHT(text, count)',
    description: 'Retorna os N últimos caracteres',
    examples: ['RIGHT("Hello", 2) → "lo"']
  },
  MID: {
    category: 'text',
    signature: 'MID(text, start, length)',
    description: 'Retorna caracteres do meio do texto',
    examples: ['MID("Hello", 2, 3) → "ell"']
  },
  LEN: {
    category: 'text',
    signature: 'LEN(text)',
    description: 'Retorna o tamanho do texto',
    examples: ['LEN("Hello") → 5']
  },
  SUBSTITUTE: {
    category: 'text',
    signature: 'SUBSTITUTE(text, old, new, instance?)',
    description: 'Substitui ocorrências de um texto',
    examples: ['SUBSTITUTE("Hello", "l", "L") → "HeLLo"']
  },
  REPLACE: {
    category: 'text',
    signature: 'REPLACE(text, start, length, new)',
    description: 'Substitui caracteres em uma posição',
    examples: ['REPLACE("Hello", 2, 3, "i") → "Hio"']
  },
  FIND: {
    category: 'text',
    signature: 'FIND(find, within, start?)',
    description: 'Encontra a posição de um texto (diferencia maiúsculas)',
    examples: ['FIND("l", "Hello") → 3']
  },
  SEARCH: {
    category: 'text',
    signature: 'SEARCH(find, within, start?)',
    description: 'Encontra a posição de um texto (sem diferenciar maiúsculas)',
    examples: ['SEARCH("L", "Hello") → 3']
  },
  REPT: {
    category: 'text',
    signature: 'REPT(text, times)',
    description: 'Repete o texto N vezes',
    examples: ['REPT("ab", 3) → "ababab"']
  },
  TEXT: {
    category: 'text',
    signature: 'TEXT(value, format)',
    description: 'Formata um número como texto',
    examples: ['TEXT(0.25, "0%") → "25%"']
  },

  // Logic functions
  IF: {
    category: 'logic',
    signature: 'IF(condition, then, else?)',
    description: 'Retorna um valor se for verdadeiro e outro se for falso',
    examples: ['IF({Age} >= 18, "Adult", "Minor")', 'IF({Score} > 90, "A", "B")']
  },
  AND: {
    category: 'logic',
    signature: 'AND(condition1, condition2, ...)',
    description: 'Retorna verdadeiro se todas as condições forem verdadeiras',
    examples: ['AND({Age} >= 18, {Consent} = true)']
  },
  OR: {
    category: 'logic',
    signature: 'OR(condition1, condition2, ...)',
    description: 'Retorna verdadeiro se alguma condição for verdadeira',
    examples: ['OR({Status} = "Active", {Status} = "Pending")']
  },
  NOT: {
    category: 'logic',
    signature: 'NOT(condition)',
    description: 'Inverte um valor verdadeiro/falso',
    examples: ['NOT(true) → false', 'NOT({Subscribed})']
  },
  XOR: {
    category: 'logic',
    signature: 'XOR(condition1, condition2, ...)',
    description: 'Retorna verdadeiro se um número ímpar de condições for verdadeiro',
    examples: ['XOR(true, false) → true']
  },
  ISBLANK: {
    category: 'logic',
    signature: 'ISBLANK(value)',
    description: 'Retorna verdadeiro se o valor estiver vazio',
    examples: ['ISBLANK({Email}) → true if empty']
  },
  ISNUMBER: {
    category: 'logic',
    signature: 'ISNUMBER(value)',
    description: 'Retorna verdadeiro se o valor for um número',
    examples: ['ISNUMBER(123) → true']
  },
  ISTEXT: {
    category: 'logic',
    signature: 'ISTEXT(value)',
    description: 'Retorna verdadeiro se o valor for texto',
    examples: ['ISTEXT("hello") → true']
  },
  IFERROR: {
    category: 'logic',
    signature: 'IFERROR(value, fallback)',
    description: 'Retorna o valor alternativo se houver erro',
    examples: ['IFERROR({Price} / {Qty}, 0)']
  },
  IFBLANK: {
    category: 'logic',
    signature: 'IFBLANK(value, fallback)',
    description: 'Retorna o valor alternativo se estiver em branco',
    examples: ['IFBLANK({Name}, "Anonymous")']
  },
  COALESCE: {
    category: 'logic',
    signature: 'COALESCE(value1, value2, ...)',
    description: 'Retorna o primeiro valor não vazio',
    examples: ['COALESCE({Nick}, {First}, "User")']
  },
  SWITCH: {
    category: 'logic',
    signature: 'SWITCH(value, case1, result1, ..., default?)',
    description: 'Compara o valor com uma lista de casos',
    examples: ['SWITCH({Grade}, "A", 4, "B", 3, "C", 2, 0)']
  },
  IFS: {
    category: 'logic',
    signature: 'IFS(cond1, result1, cond2, result2, ...)',
    description: 'Retorna o resultado da primeira condição verdadeira',
    examples: ['IFS({Score} >= 90, "A", {Score} >= 80, "B", true, "C")']
  },
  CHOOSE: {
    category: 'logic',
    signature: 'CHOOSE(index, value1, value2, ...)',
    description: 'Retorna o valor na posição indicada',
    examples: ['CHOOSE(2, "a", "b", "c") → "b"']
  },

  // Array functions
  COUNT: {
    category: 'array',
    signature: 'COUNT(value)',
    description: 'Retorna o número de itens de uma lista, ou 1 para valores não vazios',
    examples: ['COUNT({Files}) → 3', 'COUNT({Multi Select}) → 2']
  },
  ISEMPTY: {
    category: 'array',
    signature: 'ISEMPTY(value)',
    description: 'Retorna verdadeiro se o valor for nulo, texto vazio ou lista vazia',
    examples: ['ISEMPTY({Files}) → true if no files', 'ISEMPTY({Text}) → true if blank']
  },
  CONTAINS: {
    category: 'array',
    signature: 'CONTAINS(array, value)',
    description: 'Retorna verdadeiro se a lista contiver o valor (diferencia maiúsculas)',
    examples: ['CONTAINS({Tags}, "urgent") → true', 'CONTAINS({Multi Select}, "Option A")']
  },
  JOIN: {
    category: 'array',
    signature: 'JOIN(array, separator?)',
    description: 'Junta os itens da lista em um texto, com separador',
    examples: ['JOIN({Tags}, ", ") → "tag1, tag2"', 'JOIN({Multi Select}, " | ")']
  }
}

/**
 * Get functions by category
 */
export function getFunctionsByCategory(category) {
  return Object.entries(functionMeta)
    .filter(([_, meta]) => meta.category === category)
    .map(([name, meta]) => ({ name, ...meta }))
}

/**
 * Get all function names
 */
export function getFunctionNames() {
  return Object.keys(functions)
}

/**
 * Check if a function exists
 */
export function hasFunction(name) {
  return name.toUpperCase() in functions
}

/**
 * Get function by name
 */
export function getFunction(name) {
  return functions[name.toUpperCase()]
}
