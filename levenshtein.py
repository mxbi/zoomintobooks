def levenshtein_distance(str1, str2):
	m = len(str1)
	n = len(str2)
	tmp1 = list(range(0, n+1))
	tmp2 = [0]*(n+1)

	for i in range(m):
		tmp2[0] += 1
		for j in range(n):
			deletion_cost = tmp1[j+1] + 1
			insertion_cost = tmp2[j] + 1
			if str1[i] == str2[j]:
				substitution_cost = tmp1[j]
			else:
				substitution_cost = tmp1[j] + 1

			tmp2[j+1] = min(deletion_cost, insertion_cost, substitution_cost)

		tmp = tmp2.copy()
		tmp2 = tmp1.copy()
		tmp1 = tmp


	return tmp1[n]


def prefix_match(str1, str2):
	m = len(str1)
	n = len(str2)
	min_len = min(m, n)
	pref = 0

	for i in range(min_len):
		if str1[i] != str2[i]:
			break
		pref += 1

	return pref


def match(match_str, word_list):
	for word in word_list:
		word[2] = prefix_match(match_str, word[0])

	word_list.sort(key=lambda x: x[2], reverse=True)

	for word in word_list[:2]:
		word[2] = levenshtein_distance(match_str, word[0])

	word_list[:2].sort(key=lambda x: x[2])

	return word_list[:2]


print(match("nau", [["nausea from the underground", None, 0], 
	["party", None, 0],
	["nau", None, 0],
	["notes from the underground", None, 0]]))

